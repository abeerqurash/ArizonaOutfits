<?php
namespace App\Http\Controllers;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Post;
use App\Models\PostRedirect;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
class BlogController extends Controller
{
    public function index(Request $request)
    {
        $sortOptions = [
            'newest' => 'Newest first',
            'oldest' => 'Oldest first',
            'title_asc' => 'Title A-Z',
            'title_desc' => 'Title Z-A',
        ];
        $defaults = [
            'q' => '', 'category_id' => '', 'author_id' => '',
            'from' => '', 'to' => '', 'sort' => 'newest',
            'include_children' => '1', 'page' => '1',
        ];
        $input = array_replace($defaults, Arr::only($request->query(), array_keys($defaults)));
        if (is_string($input['q'])) {
            $input['q'] = trim($input['q']);
        }
        $validator = Validator::make($input, [
            'q' => ['nullable', 'string', 'max:120'],
            'category_id' => ['nullable', 'integer', 'min:1'],
            'author_id' => ['nullable', 'integer', 'min:1'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
            'sort' => ['required', Rule::in(array_keys($sortOptions))],
            'include_children' => ['required', 'boolean'],
            'page' => ['required', 'integer', 'min:1', 'max:10000'],
        ]);
        $filterErrors = $validator->errors()->toArray();
        $filters = Arr::only($validator->valid(), array_keys($defaults)) + $defaults;
        if (!empty($filters['from']) && !empty($filters['to']) && $filters['to'] < $filters['from']) {
            $filterErrors['to'] = ['The end date must be on or after the start date.'];
        }
        // Build category labels from the real hierarchy. Only publicly relevant
        // categories and their ancestors are shown, never draft-only topics.
        $allCategories = Category::query()->select(['id', 'title', 'parent_id'])
            ->orderBy('title')->get()->keyBy('id');
        $usedIds = Category::query()
            ->where(function ($query) {
                $query->whereHas('posts', fn ($posts) => $posts->published())
                    ->orWhereHas('primaryPosts', fn ($posts) => $posts->published());
            })->pluck('id');
        $visibleIds = [];
        foreach ($usedIds as $usedId) {
            $id = (int) $usedId;
            $seen = [];
            while ($id && isset($allCategories[$id]) && !isset($seen[$id])) {
                $seen[$id] = true;
                $visibleIds[$id] = true;
                $id = (int) $allCategories[$id]->parent_id;
            }
        }
        $filterCategories = collect(array_keys($visibleIds))->map(function ($id) use ($allCategories) {
            $parts = [];
            $seen = [];
            $cursor = (int) $id;
            while ($cursor && isset($allCategories[$cursor]) && !isset($seen[$cursor])) {
                $seen[$cursor] = true;
                array_unshift($parts, $allCategories[$cursor]->title);
                $cursor = (int) $allCategories[$cursor]->parent_id;
            }
            return ['id' => (int) $id, 'label' => implode(' / ', $parts)];
        })->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)->values();
        $publicAuthorIds = Post::query()->published()->whereNotNull('author_id')
            ->distinct()->pluck('author_id');
        $filterAuthors = Admin::query()->select(['id', 'name'])
            ->whereIn('id', $publicAuthorIds)->orderBy('name')->get();
        if (!empty($filters['category_id']) && !isset($visibleIds[(int) $filters['category_id']])) {
            $filterErrors['category_id'] = ['Choose a category from the available list.'];
        }
        if (!empty($filters['author_id']) && !$filterAuthors->contains('id', (int) $filters['author_id'])) {
            $filterErrors['author_id'] = ['Choose an author from the available list.'];
        }
        $query = Post::query()->with(['categories', 'primaryCategory', 'author:id,name'])->published();
        if ($filterErrors) {
            // Invalid filters show errors and no matches, rather than silently
            // broadening the query or causing a redirect loop on a GET page.
            $query->whereRaw('1 = 0');
        }
        if ($filters['q'] !== '' && $filters['q'] !== null) {
            $terms = preg_split('/\s+/u', $filters['q'], -1, PREG_SPLIT_NO_EMPTY);
            foreach ($terms as $term) {
                // Treat % and _ as text; parameters remain bound SQL values.
                $pattern = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($term)) . '%';
                $query->where(function ($match) use ($pattern) {
                    foreach (['title', 'slug', 'expert', 'excerpt', 'content'] as $field) {
                        $match->orWhereRaw("LOWER(posts.{$field}) LIKE ? ESCAPE '!'", [$pattern]);
                    }
                    $match->orWhereHas('categories', fn ($category) =>
                        $category->whereRaw("LOWER(categories.title) LIKE ? ESCAPE '!'", [$pattern]));
                    $match->orWhereHas('author', fn ($author) =>
                        $author->whereRaw("LOWER(admins.name) LIKE ? ESCAPE '!'", [$pattern]));
                });
            }
        }
        if (!empty($filters['category_id'])) {
            $categoryIds = [(int) $filters['category_id']];
            if ((bool) $filters['include_children']) {
                $seen = array_fill_keys($categoryIds, true);
                for ($i = 0; $i < count($categoryIds); $i++) {
                    foreach ($allCategories as $category) {
                        if ((int) $category->parent_id === $categoryIds[$i] && !isset($seen[$category->id])) {
                            $seen[$category->id] = true;
                            $categoryIds[] = (int) $category->id;
                        }
                    }
                }
            }
            $query->where(function ($match) use ($categoryIds) {
                $match->whereIn('primary_category_id', $categoryIds)
                    ->orWhereHas('categories', fn ($category) => $category->whereIn('categories.id', $categoryIds));
            });
        }
        if (!empty($filters['author_id'])) {
            $query->where('author_id', (int) $filters['author_id']);
        }
        if (!empty($filters['from'])) {
            $query->whereDate('published_at', '>=', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $query->whereDate('published_at', '<=', $filters['to']);
        }
        match ($filters['sort']) {
            'oldest' => $query->orderBy('published_at')->orderBy('id'),
            'title_asc' => $query->orderBy('title')->orderBy('id'),
            'title_desc' => $query->orderByDesc('title')->orderByDesc('id'),
            default => $query->orderByDesc('published_at')->orderByDesc('id'),
        };
        $urlFilters = array_filter(Arr::except($filters, ['page']), fn ($value) => $value !== '' && $value !== null);
        $posts = $query->paginate(12, ['posts.*'], 'page', (int) $filters['page'])
            ->appends($urlFilters)->fragment('article-filters');
        $activeFilters = [];
        $labels = [
            'q' => $filters['q'] !== '' && $filters['q'] !== null ? 'Search: ' . $filters['q'] : null,
            'category_id' => !empty($filters['category_id'])
                ? 'Category: ' . ($filterCategories->firstWhere('id', (int) $filters['category_id'])['label'] ?? 'Unavailable') : null,
            'author_id' => !empty($filters['author_id'])
                ? 'Author: ' . ($filterAuthors->firstWhere('id', (int) $filters['author_id'])?->name ?? 'Unavailable') : null,
            'from' => !empty($filters['from']) ? 'From: ' . $filters['from'] : null,
            'to' => !empty($filters['to']) ? 'Until: ' . $filters['to'] : null,
            'sort' => $filters['sort'] !== 'newest' ? $sortOptions[$filters['sort']] : null,
            'include_children' => !empty($filters['category_id']) && !(bool) $filters['include_children']
                ? 'Selected category only' : null,
        ];
        foreach ($labels as $key => $label) {
            if ($label === null) {
                continue;
            }
            $remaining = Arr::except($urlFilters, [$key]);
            if ($key === 'include_children') {
                $remaining['include_children'] = '1';
            }
            $activeFilters[] = ['label' => $label, 'url' => route('blogs-page', $remaining) . '#article-filters'];
        }
        return view('blogs.index', compact(
            'posts', 'filters', 'filterCategories', 'filterAuthors',
            'sortOptions', 'filterErrors', 'activeFilters'
        ));
    }
    public function show($slug)
    {
        $post = Post::with([
                'categories',
                'primaryCategory',
                'author',
            ])
            ->published()
            ->where('slug', $slug)
            ->first();
        if (!$post) {
            $redirect = PostRedirect::with([
                    'post.categories',
                    'post.primaryCategory',
                    'post.author',
                ])
                ->where('old_slug', $slug)
                ->first();
            if (!$redirect || !$redirect->post) {
                abort(404);
            }
            $redirectPost = Post::query()
                ->published()
                ->whereKey($redirect->post_id)
                ->first();
            if (!$redirectPost) {
                abort(404);
            }
            return redirect()
                ->route('blog-show', ['slug' => $redirectPost->slug], 301);
        }
        $categoryIds = $post->categories->pluck('id');
        $relatedPosts = Post::with([
                'categories',
                'primaryCategory',
                'author',
            ])
            ->published()
            ->whereHas('categories', function ($query) use ($categoryIds) {
                $query->whereIn('categories.id', $categoryIds);
            })
            ->where('id', '!=', $post->id)
            ->inRandomOrder()
            ->take(6)
            ->get();
        $latestPosts = Post::with([
                'categories',
                'primaryCategory',
                'author',
            ])
            ->published()
            ->where('id', '!=', $post->id)
            ->orderByRaw('COALESCE(published_at, scheduled_at) DESC')
            ->orderByDesc('id')
            ->take(4)
            ->get();
        $popularCategories = Category::query()
            ->withCount([
                'posts as published_posts_count' => function ($query) {
                    $query->published();
                },
            ])
            ->whereHas('posts', function ($query) {
                $query->published();
            })
            ->orderByDesc('published_posts_count')
            ->orderBy('title')
            ->take(9)
            ->get();
        $reviewProducts = Product::query()
            ->where('status', 'active')
            ->whereHas('approvedReviews')
            ->with([
                'images' => fn ($query) => $query
                    ->orderBy('sort_order')
                    ->orderBy('id'),
                'approvedReviews' => fn ($query) => $query
                    ->latest()
                    ->limit(20),
            ])
            ->latest('id')
            ->take(10)
            ->get();
        // Kept temporarily until the existing review Blade is replaced.
        $blogReviews = Review::query()
            ->approved()
            ->with([
                'product.images',
                'user',
            ])
            ->latest()
            ->take(6)
            ->get();
        $bladePath = 'blogs.posts.' . ($post->template ?: $post->slug);
        if (!View::exists($bladePath)) {
            abort(404, 'Post view not found.');
        }
        return view($bladePath, [
            'post' => $post,
            'latestPosts' => $latestPosts,
            'relatedPosts' => $relatedPosts,
            'popularCategories' => $popularCategories,
            'blogReviews' => $blogReviews,
            'reviewProducts' => $reviewProducts,
            'title' => $post->meta_title ?? $post->title,
            'meta_description' => $post->meta_description,
        ]);
    }
}