<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use App\Models\Category;

use App\Models\Post;

use App\Models\PostRedirect;
use App\Models\PostRevision;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;

use Illuminate\Support\Str;

use Illuminate\Validation\Rule;

use Illuminate\Validation\ValidationException;

class PostController extends AdminController

{

    public function index(Request $request)

    {

        $query = Post::query()

            ->with(['categories:id,title,slug,parent_id', 'primaryCategory:id,title,slug', 'author:id,name'])

            ->withCount('categories');

        if ($search = trim((string) $request->input('search'))) {

            $query->where(function ($builder) use ($search) {

                $builder

                    ->where('title', 'like', "%{$search}%")

                    ->orWhere('slug', 'like', "%{$search}%")

                    ->orWhere('expert', 'like', "%{$search}%")

                    ->orWhere('excerpt', 'like', "%{$search}%");

            });

        }

        if ($status = $request->input('status')) {

            if (array_key_exists($status, Post::statuses())) {

                $query->where('status', $status);

            }

        }

        if ($categoryId = $request->integer('category_id')) {

            $query->where(function ($builder) use ($categoryId) {

                $builder

                    ->where('primary_category_id', $categoryId)

                    ->orWhereHas('categories', fn ($categoryQuery) => $categoryQuery->where('categories.id', $categoryId));

            });

        }

        $posts = $query

            ->latest('updated_at')

            ->paginate(10)

            ->withQueryString();

        $categories = Category::roots()

            ->with('childrenRecursive')

            ->ordered()

            ->get();

        $statuses = Post::statuses();

        $stats = [

            'total' => Post::count(),

            'draft' => Post::where('status', Post::STATUS_DRAFT)->count(),

            'published' => Post::where('status', Post::STATUS_PUBLISHED)->count(),

            'scheduled' => Post::where('status', Post::STATUS_SCHEDULED)->count(),

            'archived' => Post::where('status', Post::STATUS_ARCHIVED)->count(),

        ];

        return view('admin.posts.index', compact(

            'posts',

            'categories',

            'statuses',

            'stats'

        ));

    }

    public function create()

    {

        $categories = Category::roots()

            ->with('childrenRecursive')

            ->ordered()

            ->get();

        return view('admin.posts.create', [

            'categories' => $categories,

            'statuses' => Post::statuses(),

            'selectedCategoryIds' => [],

        ]);

    }

    public function store(Request $request)

    {

        $validated = $this->validatePost($request);

        $categoryIds = $this->normalizeCategoryIds($validated['categories'] ?? []);

        $primaryCategoryId = $this->normalizePrimaryCategoryId(

            $validated['primary_category_id'] ?? null,

            $categoryIds

        );

        $featureImage = null;

        $ogImage = null;

        try {

            if ($request->hasFile('feature_image_upload')) {

                $featureImage = $request->file('feature_image_upload')->store('posts', 'public');

            } elseif (!empty($validated['feature_image'])) {

                $featureImage = $this->normalizeMediaPath($validated['feature_image']);

            }

            if ($request->hasFile('og_image_upload')) {

                $ogImage = $request->file('og_image_upload')->store('posts/social', 'public');

            } elseif (!empty($validated['og_image'])) {

                $ogImage = $this->normalizeMediaPath($validated['og_image']);

            }

            $post = DB::transaction(function () use (

                $request,

                $validated,

                $categoryIds,

                $primaryCategoryId,

                $featureImage,

                $ogImage

            ) {

                $status = $validated['status'] ?? Post::STATUS_DRAFT;

                [$publishedAt, $scheduledAt] = $this->resolvePublishingDates(

                    $status,

                    $validated['published_at'] ?? null,

                    $validated['scheduled_at'] ?? null

                );

                $post = Post::create([

                    'title' => trim($validated['title']),

                    'slug' => $this->uniqueSlug($validated['slug'] ?? null, $validated['title']),

                    'expert' => $validated['expert'] ?? null,

                    'excerpt' => $validated['excerpt'] ?? null,

                    'content' => app(\App\Services\HtmlContentSanitizer::class)->clean($validated['content'] ?? null),

                    'feature_image' => $featureImage,

                    'feature_image_alt' => $validated['feature_image_alt'] ?? null,

                    'template' => !empty($validated['template'])

                        ? trim($validated['template'])

                        : Str::slug($validated['title']),

                    'primary_category_id' => $primaryCategoryId,

                    'author_id' => auth('admin')->id(),

                    'status' => $status,

                    'published_at' => $publishedAt,

                    'scheduled_at' => $scheduledAt,

                    'meta_title' => $validated['meta_title'] ?? null,

                    'meta_description' => $validated['meta_description'] ?? null,

                    'canonical_url' => $validated['canonical_url'] ?? null,

                    'robots_index' => $request->boolean('robots_index'),

                    'robots_follow' => $request->boolean('robots_follow'),

                    'og_title' => $validated['og_title'] ?? null,

                    'og_description' => $validated['og_description'] ?? null,

                    'og_image' => $ogImage,

                ]);

                $post->categories()->sync($categoryIds);

                return $post;

            });

        } catch (\Throwable $e) {

            $this->deleteManagedMedia($featureImage, 'posts/');

            $this->deleteManagedMedia($ogImage, 'posts/social/');

            throw $e;

        }

        return redirect()

            ->route('admin.posts.edit', $post)

            ->with('success', 'Blog post created successfully.');

    }

    public function show(Post $post)

    {

        return redirect()->route('admin.posts.edit', $post);

    }

    public function edit(Post $post)

    {

        $post->load([
            'categories:id,title,slug,parent_id',
            'primaryCategory',
            'author',
            'revisions.admin:id,name',
        ]);

        $categories = Category::roots()

            ->with('childrenRecursive')

            ->ordered()

            ->get();

        return view('admin.posts.edit', [

            'post' => $post,

            'categories' => $categories,

            'statuses' => Post::statuses(),

            'selectedCategoryIds' => $post->categories->pluck('id')->map(fn ($id) => (int) $id)->all(),

        ]);

    }

    public function update(Request $request, Post $post)

    {

        $validated = $this->validatePost($request, $post);

        $categoryIds = $this->normalizeCategoryIds($validated['categories'] ?? []);

        $primaryCategoryId = $this->normalizePrimaryCategoryId(

            $validated['primary_category_id'] ?? null,

            $categoryIds

        );

        $oldSlug = $post->slug;

        $oldFeatureImage = $post->feature_image;

        $oldOgImage = $post->og_image;

        $newFeatureImage = null;

        $newOgImage = null;

        try {

            $featureImage = $oldFeatureImage;

            if ($request->hasFile('feature_image_upload')) {

                $newFeatureImage = $request->file('feature_image_upload')->store('posts', 'public');

                $featureImage = $newFeatureImage;

            } elseif ($request->boolean('remove_feature_image')) {

                $featureImage = null;

            } elseif (array_key_exists('feature_image', $validated) && trim((string) $validated['feature_image']) !== '') {

                $featureImage = $this->normalizeMediaPath($validated['feature_image']);

            }

            $ogImage = $oldOgImage;

            if ($request->hasFile('og_image_upload')) {

                $newOgImage = $request->file('og_image_upload')->store('posts/social', 'public');

                $ogImage = $newOgImage;

            } elseif ($request->boolean('remove_og_image')) {

                $ogImage = null;

            } elseif (array_key_exists('og_image', $validated) && trim((string) $validated['og_image']) !== '') {

                $ogImage = $this->normalizeMediaPath($validated['og_image']);

            }

            DB::transaction(function () use (

                $request,

                $validated,

                $post,

                $oldSlug,

                $categoryIds,

                $primaryCategoryId,

                $featureImage,

                $ogImage

            ) {
                $post = Post::query()->lockForUpdate()->findOrFail($post->id);
                $oldSlug = $post->slug;

                // Preserve the latest saved image if this request did not edit it.
                if (!$request->hasFile('feature_image_upload')
                    && !$request->boolean('remove_feature_image')
                    && trim((string) ($validated['feature_image'] ?? '')) === '') {
                    $featureImage = $post->feature_image;
                }
                if (!$request->hasFile('og_image_upload')
                    && !$request->boolean('remove_og_image')
                    && trim((string) ($validated['og_image'] ?? '')) === '') {
                    $ogImage = $post->og_image;
                }

                $status = $validated['status'] ?? Post::STATUS_DRAFT;

                [$publishedAt, $scheduledAt] = $this->resolvePublishingDates(

                    $status,

                    $validated['published_at'] ?? null,

                    $validated['scheduled_at'] ?? null,

                    $post

                );

                $newSlug = $this->uniqueSlug(

                    $validated['slug'] ?? null,

                    $validated['title'],

                    $post->id

                );

                $this->createRevisionSnapshot($post);

                $post->update([

                    'title' => trim($validated['title']),

                    'slug' => $newSlug,

                    'expert' => $validated['expert'] ?? null,

                    'excerpt' => $validated['excerpt'] ?? null,

                    'content' => app(\App\Services\HtmlContentSanitizer::class)->clean($validated['content'] ?? null),

                    'feature_image' => $featureImage,

                    'feature_image_alt' => $validated['feature_image_alt'] ?? null,

                    'template' => !empty($validated['template'])

                        ? trim($validated['template'])

                        : ($post->template ?: Str::slug($validated['title'])),

                    'primary_category_id' => $primaryCategoryId,

                    'author_id' => $post->author_id ?: auth('admin')->id(),

                    'status' => $status,

                    'published_at' => $publishedAt,

                    'scheduled_at' => $scheduledAt,

                    'meta_title' => $validated['meta_title'] ?? null,

                    'meta_description' => $validated['meta_description'] ?? null,

                    'canonical_url' => $validated['canonical_url'] ?? null,

                    'robots_index' => $request->boolean('robots_index'),

                    'robots_follow' => $request->boolean('robots_follow'),

                    'og_title' => $validated['og_title'] ?? null,

                    'og_description' => $validated['og_description'] ?? null,

                    'og_image' => $ogImage,

                ]);

                $post->categories()->sync($categoryIds);

                $this->syncSlugRedirects($post, $oldSlug);

            });

        } catch (\Throwable $e) {

            $this->deleteManagedMedia($newFeatureImage, 'posts/');

            $this->deleteManagedMedia($newOgImage, 'posts/social/');

            throw $e;

        }

        if ($oldFeatureImage !== $post->fresh()->feature_image) {

            $this->deleteManagedMedia($oldFeatureImage, 'posts/');

        }

        if ($oldOgImage !== $post->fresh()->og_image) {

            $this->deleteManagedMedia($oldOgImage, 'posts/social/');

        }

        return redirect()

            ->route('admin.posts.edit', $post)

            ->with('success', 'Blog post updated successfully.');

    }

    public function restoreRevision(Post $post, PostRevision $revision)
    {
        // Route parameters must identify a revision belonging to this post.
        abort_unless((int) $revision->post_id === (int) $post->id, 404);

        $notices = [];

        try {
            DB::transaction(function () use ($post, $revision, &$notices): void {
                $lockedPost = Post::query()->lockForUpdate()->findOrFail($post->id);
                $lockedRevision = PostRevision::query()
                    ->where('post_id', $lockedPost->id)
                    ->lockForUpdate()
                    ->findOrFail($revision->id);

                [$attributes, $categoryIds] = $this->validatedRevisionSnapshot(
                    $lockedPost,
                    $lockedRevision,
                    $notices
                );

                $slugOwner = Post::query()
                    ->where('slug', $attributes['slug'])
                    ->whereKeyNot($lockedPost->id)
                    ->lockForUpdate()
                    ->first();
                $redirectOwner = PostRedirect::query()
                    ->where('old_slug', $attributes['slug'])
                    ->lockForUpdate()
                    ->first();

                if (app(\App\Services\PublicSlugRegistry::class)->occupied($attributes['slug'],null,$lockedPost->id) || $slugOwner || ($redirectOwner
                    && (int) $redirectOwner->post_id !== (int) $lockedPost->id)) {
                    throw ValidationException::withMessages([
                        'revision' => 'The saved slug belongs to another article or its redirect history. Nothing was restored.',
                    ]);
                }

                // Preserve today's saved state before replacing any fields/pivot rows.
                $this->createRevisionSnapshot($lockedPost);
                $oldSlug = $lockedPost->slug;
                $lockedPost->update($attributes);
                $lockedPost->categories()->sync($categoryIds);
                $this->syncSlugRedirects($lockedPost, $oldSlug);

                // Media is deliberately not deleted during restore. Both versions
                // remain referenced by the original and the new safety revisions.
            }, 3);
        } catch (ValidationException $exception) {
            return redirect()->route('admin.posts.edit', $post)
                ->withErrors($exception->errors())
                ->with('error', 'Revision restore was cancelled. Your saved post is unchanged.');
        }

        return redirect()->route('admin.posts.edit', $post)->with(
            'success',
            'Revision #' . $revision->revision_number
                . ' restored. The previous saved version was added to revision history.'
                . ($notices ? ' ' . implode(' ', array_unique($notices)) : '')
        );
    }

    private function validatedRevisionSnapshot(Post $post, PostRevision $revision, array &$notices): array
    {
        $snapshot = $revision->snapshot;
        $fields = $post->getFillable();

        if (!is_array($snapshot)
            || !isset($snapshot['post'], $snapshot['category_ids'])
            || !is_array($snapshot['post'])
            || !is_array($snapshot['category_ids'])
            || !array_is_list($snapshot['category_ids'])
            || array_diff($fields, array_keys($snapshot['post'])) !== []) {
            throw ValidationException::withMessages([
                'revision' => 'This revision has an incomplete or invalid snapshot. Nothing was restored.',
            ]);
        }

        if (isset($snapshot['post']['id'])
            && (int) $snapshot['post']['id'] !== (int) $post->id) {
            throw ValidationException::withMessages([
                'revision' => 'The snapshot does not belong to this article.',
            ]);
        }

        // Restore only supported post fields, never IDs, timestamps or appended URLs.
        $attributes = array_intersect_key($snapshot['post'], array_flip($fields));
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'expert' => ['nullable', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'feature_image' => ['nullable', 'string', 'max:2048'],
            'feature_image_alt' => ['nullable', 'string', 'max:255'],
            'template' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_-]+(?:\.[a-zA-Z0-9_-]+)*$/'],
            'primary_category_id' => ['nullable', 'integer', 'min:1'],
            'author_id' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', Rule::in(array_keys(Post::statuses()))],
            'published_at' => ['nullable', 'required_if:status,published', 'date'],
            'scheduled_at' => ['nullable', 'required_if:status,scheduled', 'date'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'canonical_url' => ['nullable', 'url', 'max:2048'],
            'robots_index' => ['required', 'boolean'],
            'robots_follow' => ['required', 'boolean'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string'],
            'og_image' => ['nullable', 'string', 'max:2048'],
            'category_ids' => ['present', 'array'],
            'category_ids.*' => ['integer', 'min:1'],
        ];
        $validator = Validator::make($attributes + ['category_ids' => $snapshot['category_ids']], $rules);
        if ($validator->fails()) {
            throw ValidationException::withMessages([
                'revision' => 'The saved snapshot is invalid: ' . $validator->errors()->first(),
            ]);
        }

        foreach (['feature_image', 'og_image'] as $field) {
            $path = $attributes[$field];
            if ($path !== null && !$this->isValidManualMediaPath($path)) {
                throw ValidationException::withMessages([
                    'revision' => 'A saved image path is invalid. Nothing was restored.',
                ]);
            }
            $managedPath = $this->managedMediaPath($path);
            if ($managedPath !== null && str_starts_with($managedPath, 'posts/')
                && !Storage::disk('public')->exists($managedPath)) {
                throw ValidationException::withMessages([
                    'revision' => 'A saved image file is missing (' . $managedPath
                        . '). Restore that file from a backup first. Nothing was restored.',
                ]);
            }
        }

        $template = 'blogs.posts.' . ($attributes['template'] ?: $attributes['slug']);
        if ($attributes['status'] === Post::STATUS_PUBLISHED && !View::exists($template)) {
            throw ValidationException::withMessages([
                'revision' => 'The saved published article template is missing. Restore its Blade file first.',
            ]);
        }

        $categoryIds = $this->normalizeCategoryIds($snapshot['category_ids']);
        $primaryId = $attributes['primary_category_id'];
        $requestedIds = $categoryIds;
        if ($primaryId !== null) {
            $requestedIds[] = (int) $primaryId;
        }
        $existingIds = Category::query()->whereIn('id', $requestedIds)
            ->lockForUpdate()->pluck('id')->map(fn ($id) => (int) $id)->all();
        if (array_diff($requestedIds, $existingIds) !== []) {
            $notices[] = 'Categories that have since been deleted were skipped.';
        }
        $categoryIds = array_values(array_intersect($categoryIds, $existingIds));
        $primaryId = $primaryId !== null && in_array((int) $primaryId, $existingIds, true)
            ? (int) $primaryId : null;
        $attributes['primary_category_id'] = $this->normalizePrimaryCategoryId($primaryId, $categoryIds);

        if ($attributes['author_id'] !== null
            && !Admin::query()->whereKey($attributes['author_id'])->lockForUpdate()->exists()) {
            $attributes['author_id'] = null;
            $notices[] = 'The saved author no longer exists, so the author was cleared.';
        }

        return [$attributes, $categoryIds];
    }

    private function createRevisionSnapshot(Post $post): void
    {
        // Callers hold a lock on the post row, shared by normal updates/restores.
        $number = ((int) PostRevision::query()->where('post_id', $post->id)
            ->max('revision_number')) + 1;

        PostRevision::create([
            'post_id' => $post->id,
            'admin_id' => auth('admin')->id(),
            'revision_number' => $number,
            'snapshot' => [
                'post' => $post->attributesToArray(),
                'category_ids' => $post->categories()->pluck('categories.id')
                    ->map(fn ($id) => (int) $id)->values()->all(),
            ],
        ]);
    }

    private function syncSlugRedirects(Post $post, string $oldSlug): void
    {
        // A reclaimed historical slug becomes the current URL, not a self-redirect.
        PostRedirect::query()->where('post_id', $post->id)
            ->where('old_slug', $post->slug)->delete();

        if ($oldSlug === $post->slug) {
            return;
        }

        $redirect = PostRedirect::firstOrCreate(
            ['old_slug' => $oldSlug],
            ['post_id' => $post->id]
        );
        if ((int) $redirect->post_id !== (int) $post->id) {
            throw ValidationException::withMessages([
                'revision' => 'The previous URL belongs to another article redirect. No changes were saved.',
            ]);
        }
        // Redirects point to the post ID; all old URLs resolve to its latest slug.
    }

    public function destroy(Post $post)

    {

        $featureImage = $post->feature_image;

        $ogImage = $post->og_image;

        DB::transaction(function () use ($post) {

            $post->categories()->detach();

            $post->delete();

        });

        $this->deleteManagedMedia($featureImage, 'posts/');

        $this->deleteManagedMedia($ogImage, 'posts/social/');

        return redirect()

            ->route('admin.posts.index')

            ->with('success', 'Blog post deleted successfully.');

    }

    private function validatePost(Request $request, ?Post $post = null): array

    {

        $postId = $post?->id;

        $validated = $request->validate([

            'title' => ['required', 'string', 'max:255'],

            'slug' => [

                'nullable',

                'string',

                'max:255',

                Rule::unique('posts', 'slug')->ignore($postId),

            ],

            'expert' => ['nullable', 'string'],

            'excerpt' => ['nullable', 'string'],

            'content' => ['nullable', 'string'],

            'feature_image' => [

                'nullable',

                'string',

                'max:2048',

                function (string $attribute, mixed $value, \Closure $fail): void {

                    if (!$this->isValidManualMediaPath((string) $value)) {

                        $fail('The featured image path must be a valid JPG, JPEG, PNG or WebP path or an http/https image URL.');

                    }

                },

            ],

            'feature_image_upload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],

            'feature_image_alt' => ['nullable', 'string', 'max:255'],

            'remove_feature_image' => ['nullable', 'boolean'],

            'template' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_-]+(?:\.[a-zA-Z0-9_-]+)*$/'],

            'categories' => ['nullable', 'array'],

            'categories.*' => ['integer', 'exists:categories,id'],

            'primary_category_id' => ['nullable', 'integer', 'exists:categories,id'],

            'status' => ['required', Rule::in(array_keys(Post::statuses()))],

            'published_at' => ['nullable', 'date'],

            'scheduled_at' => [

                'nullable',

                'date',

                Rule::requiredIf(fn () => $request->input('status') === Post::STATUS_SCHEDULED),

            ],

            'meta_title' => ['nullable', 'string', 'max:255'],

            'meta_description' => ['nullable', 'string'],

            'canonical_url' => ['nullable', 'url', 'max:2048'],

            'robots_index' => ['nullable', 'boolean'],

            'robots_follow' => ['nullable', 'boolean'],

            'og_title' => ['nullable', 'string', 'max:255'],

            'og_description' => ['nullable', 'string'],

            'og_image' => [

                'nullable',

                'string',

                'max:2048',

                function (string $attribute, mixed $value, \Closure $fail): void {

                    if (!$this->isValidManualMediaPath((string) $value)) {

                        $fail('The social image path must be a valid JPG, JPEG, PNG or WebP path or an http/https image URL.');

                    }

                },

            ],

            'og_image_upload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],

            'remove_og_image' => ['nullable', 'boolean'],

        ]);
        $status = $validated['status'];
        $templateName = !empty($validated['template']) ? trim($validated['template']) : ($post?->template ?: Str::slug($validated['title']));
        if (in_array($status, [Post::STATUS_PUBLISHED, Post::STATUS_SCHEDULED], true)
            && !View::exists('blogs.posts.' . $templateName)) {
            throw ValidationException::withMessages(['template' => 'Create the custom article Blade file in resources/views/blogs/posts before publishing or scheduling. Save as Draft until the file is ready.']);
        }
        return $validated;


    }

    private function normalizeCategoryIds(array $categoryIds): array

    {

        return collect($categoryIds)

            ->map(fn ($id) => (int) $id)

            ->filter(fn ($id) => $id > 0)

            ->unique()

            ->values()

            ->all();

    }

    private function normalizePrimaryCategoryId($primaryCategoryId, array &$categoryIds): ?int

    {

        if (!$primaryCategoryId) {

            return null;

        }

        $primaryCategoryId = (int) $primaryCategoryId;

        if (!in_array($primaryCategoryId, $categoryIds, true)) {

            $categoryIds[] = $primaryCategoryId;

        }

        return $primaryCategoryId;

    }

    private function uniqueSlug(?string $requestedSlug, string $title, ?int $ignoreId = null): string

    {

        $base = Str::slug($requestedSlug ?: $title);

        if ($base === '') {

            $base = 'post';

        }

        $slug = $base;

        $counter = 2;

        while(app(\App\Services\PublicSlugRegistry::class)->occupied($slug,null,$ignoreId)){
            $slug=$base.'-'.$counter++;
        }

        return $slug;

    }

    private function resolvePublishingDates(

        string $status,

        $publishedAt,

        $scheduledAt,

        ?Post $post = null

    ): array {

        if ($status === Post::STATUS_PUBLISHED) {

            return [

                $publishedAt ?: $post?->published_at ?: now(),

                null,

            ];

        }

        if ($status === Post::STATUS_SCHEDULED) {

            return [

                null,

                $scheduledAt,

            ];

        }

        return [null, null];

    }

    private function normalizeMediaPath(string $path): string

    {

        return trim($path);

    }

    private function isValidManualMediaPath(string $path): bool

    {

        $path = trim($path);

        if ($path === '') {

            return true;

        }
        if (preg_match("/[\s<>\"']/", $path)) {

            return false;

        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {

            $scheme = strtolower((string) parse_url($path, PHP_URL_SCHEME));

            if (!in_array($scheme, ['http', 'https'], true)) {

                return false;

            }

            $pathOnly = (string) parse_url($path, PHP_URL_PATH);

        } else {

            $pathOnly = strtok($path, '?#') ?: $path;

        }

        $extension = strtolower((string) pathinfo($pathOnly, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true);

    }

    private function managedMediaPath(?string $path): ?string
    {
        if (!$path || filter_var($path, FILTER_VALIDATE_URL)) {
            return null;
        }
        $normalized = ltrim(trim($path), '/');
        if (str_starts_with($normalized, 'storage/')) {
            $normalized = substr($normalized, 8);
        }
        // Only ordinary relative paths may identify files on the public disk.
        if ($normalized === '' || str_contains($normalized, chr(92))
            || preg_match('~(?:^|/)\.{1,2}(?:/|$)|[:?#]~', $normalized)) {
            return null;
        }
        return $normalized;
    }

    private function deleteManagedMedia(?string $path, string $requiredPrefix): void
    {
        $normalized = $this->managedMediaPath($path);
        if ($normalized === null || !str_starts_with($normalized, $requiredPrefix)) {
            return;
        }

        // Keep files used by any live article, including shared featured/OG images.
        foreach (Post::query()->select(['id', 'feature_image', 'og_image'])->cursor() as $otherPost) {
            foreach (['feature_image', 'og_image'] as $field) {
                if ($this->managedMediaPath($otherPost->{$field}) === $normalized) {
                    return;
                }
            }
        }

        // Updating/removing an image must not destroy a revision's historical file.
        foreach (PostRevision::query()->select(['id', 'snapshot'])->cursor() as $revision) {
            $saved = $revision->snapshot['post'] ?? null;
            if (!is_array($saved)) {
                continue;
            }
            foreach (['feature_image', 'og_image'] as $field) {
                $value = $saved[$field] ?? null;
                if (is_string($value) && $this->managedMediaPath($value) === $normalized) {
                    return;
                }
            }
        }

        if (Storage::disk('public')->exists($normalized)) {
            Storage::disk('public')->delete($normalized);
        }
    }
}
