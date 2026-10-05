<?php
namespace App\Http\Controllers\Admin;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
class CategoryController extends AdminController
{
    public function index(Request $request)
    {
        $query = Category::query()
            ->with('parent:id,title,slug')
            ->withCount(['posts', 'children']);
        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('expert', 'like', "%{$search}%");
            });
        }
        $categories = $query
            ->orderByRaw('CASE WHEN parent_id IS NULL THEN 0 ELSE 1 END')
            ->orderBy('parent_id')
            ->orderBy('title')
            ->paginate(20)
            ->withQueryString();
        $tree = Category::roots()
            ->with([
                'childrenRecursive' => fn ($query) => $query->withCount(['posts', 'children']),
            ])
            ->withCount(['posts', 'children'])
            ->ordered()
            ->get();
        $stats = [
            'total' => Category::count(),
            'roots' => Category::whereNull('parent_id')->count(),
            'children' => Category::whereNotNull('parent_id')->count(),
        ];
        return view('admin.categories.index', compact(
            'categories',
            'tree',
            'stats'
        ));
    }
    public function create()
    {
        return view('admin.categories.create', [
            'parentOptions' => $this->parentOptions(),
        ]);
    }
    public function store(Request $request)
    {
        $validated = $this->validateCategory($request);
        $image = null;
        try {
            if ($request->hasFile('image')) {
                $image = $request->file('image')->store('categories', 'public');
            }
            $category = Category::create([
                'title' => trim($validated['title']),
                'slug' => $this->uniqueSlug($validated['slug'] ?? null, $validated['title']),
                'parent_id' => $validated['parent_id'] ?? null,
                'image' => $image,
                'meta_title' => $validated['meta_title'] ?? null,
                'meta_description' => $validated['meta_description'] ?? null,
                'expert' => $validated['expert'] ?? null,
            ]);
        } catch (\Throwable $e) {
            $this->deleteManagedImage($image);
            throw $e;
        }
        return redirect()
            ->route('admin.categories.edit', $category)
            ->with('success', 'Blog category created successfully.');
    }
    public function show(Category $category)
    {
        return redirect()->route('admin.categories.edit', $category);
    }
    public function edit(Category $category)
    {
        $category->load(['parent', 'children']);
        return view('admin.categories.edit', [
            'category' => $category,
            'parentOptions' => $this->parentOptions($category),
        ]);
    }
    public function update(Request $request, Category $category)
    {
        $validated = $this->validateCategory($request, $category);
        $parentId = !empty($validated['parent_id'])
            ? (int) $validated['parent_id']
            : null;
        $this->assertValidParent($category, $parentId);
        $oldImage = $category->image;
        $newImage = null;
        $image = $oldImage;
        try {
            if ($request->hasFile('image')) {
                $newImage = $request->file('image')->store('categories', 'public');
                $image = $newImage;
            } elseif ($request->boolean('remove_image')) {
                $image = null;
            }
            $category->update([
                'title' => trim($validated['title']),
                'slug' => $this->uniqueSlug(
                    $validated['slug'] ?? null,
                    $validated['title'],
                    $category->id
                ),
                'parent_id' => $parentId,
                'image' => $image,
                'meta_title' => $validated['meta_title'] ?? null,
                'meta_description' => $validated['meta_description'] ?? null,
                'expert' => $validated['expert'] ?? null,
            ]);
        } catch (\Throwable $e) {
            $this->deleteManagedImage($newImage);
            throw $e;
        }
        if ($oldImage !== $category->fresh()->image) {
            $this->deleteManagedImage($oldImage);
        }
        return redirect()
            ->route('admin.categories.edit', $category)
            ->with('success', 'Blog category updated successfully.');
    }
    public function destroy(Category $category)
    {
        $category->loadCount(['children', 'posts', 'primaryPosts']);
        if ($category->children_count > 0) {
            return redirect()
                ->route('admin.categories.index')
                ->with(
                    'error',
                    'This category has child categories. Move or delete the child categories first.'
                );
        }
        if ($category->posts_count > 0 || $category->primary_posts_count > 0) {
            return redirect()
                ->route('admin.categories.index')
                ->with(
                    'error',
                    'This category is used by blog posts. Reassign those posts before deleting the category.'
                );
        }
        $image = $category->image;
        DB::transaction(function () use ($category) {
            $category->delete();
        });
        $this->deleteManagedImage($image);
        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Blog category deleted successfully.');
    }
    private function validateCategory(Request $request, ?Category $category = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('categories', 'slug')->ignore($category?->id),
            ],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'expert' => ['nullable', 'string'],
        ]);
    }
    private function parentOptions(?Category $editingCategory = null): array
    {
        $excludedIds = [];
        if ($editingCategory) {
            $excludedIds = array_merge(
                [$editingCategory->id],
                $editingCategory->descendantIds()
            );
        }
        $roots = Category::roots()
            ->with('childrenRecursive')
            ->ordered()
            ->get();
        $options = [];
        foreach ($roots as $root) {
            $this->flattenCategoryTree(
                $root,
                $options,
                0,
                $excludedIds
            );
        }
        return $options;
    }
    private function flattenCategoryTree(
        Category $category,
        array &$options,
        int $depth,
        array $excludedIds
    ): void {
        if (!in_array((int) $category->id, array_map('intval', $excludedIds), true)) {
            $options[] = [
                'id' => (int) $category->id,
                'title' => $category->title,
                'depth' => $depth,
            ];
        }
        foreach ($category->children as $child) {
            $this->flattenCategoryTree(
                $child,
                $options,
                $depth + 1,
                $excludedIds
            );
        }
    }
    private function assertValidParent(Category $category, ?int $parentId): void
    {
        if (!$parentId) {
            return;
        }
        if ($parentId === (int) $category->id) {
            throw ValidationException::withMessages([
                'parent_id' => 'A category cannot be its own parent.',
            ]);
        }
        if (in_array($parentId, $category->descendantIds(), true)) {
            throw ValidationException::withMessages([
                'parent_id' => 'A category cannot be moved inside one of its own child categories.',
            ]);
        }
    }
    private function uniqueSlug(
        ?string $requestedSlug,
        string $title,
        ?int $ignoreId = null
    ): string {
        $base = Str::slug($requestedSlug ?: $title);
        if ($base === '') {
            $base = 'category';
        }
        $slug = $base;
        $counter = 2;
        while (
            Category::query()
                ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base . '-' . $counter;
            $counter++;
        }
        return $slug;
    }
    private function deleteManagedImage(?string $path): void
    {
        if (!$path || filter_var($path, FILTER_VALIDATE_URL)) {
            return;
        }
        $normalized = ltrim($path, '/');
        if (str_starts_with($normalized, 'storage/')) {
            $normalized = substr($normalized, 8);
        }
        if (str_contains($normalized, chr(92)) || preg_match('~(?:^|/)\.{1,2}(?:/|$)|[:?#]~', $normalized)) return;
        if (!str_starts_with($normalized, 'categories/')) {
            return;
        }
        foreach (Category::query()->whereNotNull('image')->pluck('image') as $usedPath) {
            $used = ltrim((string) $usedPath, '/');
            if (str_starts_with($used, 'storage/')) $used = substr($used, 8);
            if ($used === $normalized) return;
        }
        if (Storage::disk('public')->exists($normalized)) {
            Storage::disk('public')->delete($normalized);
        }
    }
}