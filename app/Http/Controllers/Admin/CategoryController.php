<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends AdminController
{
    /*
    |--------------------------------------------------------------------------
    | Categories List
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $categories = Category::with('parent')
            ->latest()
            ->paginate(10);

        return view(
            'admin.categories.index',
            compact('categories')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create Category
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $parents = Category::whereNull('parent_id')
            ->orderBy('title')
            ->get();

        return view(
            'admin.categories.create',
            compact('parents')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store Category
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:categories,slug',
            ],

            'parent_id' => [
                'nullable',
                'exists:categories,id',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'meta_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'meta_description' => [
                'nullable',
                'string',
            ],

            'expert' => [
                'nullable',
                'string',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Generate Slug
        |--------------------------------------------------------------------------
        */

        $data['slug'] = !empty($data['slug'])
            ? Str::slug($data['slug'])
            : Str::slug($data['title']);


        /*
        |--------------------------------------------------------------------------
        | Upload Category Image
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('image')) {

            $image = $request->file('image');

            $extension = strtolower(
                $image->getClientOriginalExtension()
            );

            $filename =
                Str::slug($data['title'])
                . '-'
                . Str::uuid()
                . '.'
                . $extension;

            $path = $image->storeAs(
                'categories',
                $filename,
                'public'
            );

            $data['image'] = $path;
        }


        /*
        |--------------------------------------------------------------------------
        | Create Category
        |--------------------------------------------------------------------------
        */

        try {

            Category::create($data);

        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Delete uploaded image if database creation fails
            |--------------------------------------------------------------------------
            */

            if (!empty($data['image'])) {
                Storage::disk('public')->delete(
                    $data['image']
                );
            }

            throw $e;
        }


        return redirect()
            ->route('admin.categories.index')
            ->with(
                'success',
                'Category created successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Edit Category
    |--------------------------------------------------------------------------
    */

    public function edit(Category $category)
    {
        $parents = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->orderBy('title')
            ->get();

        return view(
            'admin.categories.edit',
            compact(
                'category',
                'parents'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Category
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Category $category
    ) {
        $data = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'required',
                'string',
                'max:255',
                'unique:categories,slug,' . $category->id,
            ],

            'parent_id' => [
                'nullable',
                'exists:categories,id',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'remove_image' => [
                'nullable',
                'boolean',
            ],

            'meta_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'meta_description' => [
                'nullable',
                'string',
            ],

            'expert' => [
                'nullable',
                'string',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Normalize Slug
        |--------------------------------------------------------------------------
        */

        $data['slug'] = Str::slug(
            $data['slug']
        );


        /*
        |--------------------------------------------------------------------------
        | Keep Old Image
        |--------------------------------------------------------------------------
        */

        $oldImage = $category->image;

        $newImage = null;


        /*
        |--------------------------------------------------------------------------
        | Upload New Image
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('image')) {

            $image = $request->file('image');

            $extension = strtolower(
                $image->getClientOriginalExtension()
            );

            $filename =
                Str::slug($data['title'])
                . '-'
                . Str::uuid()
                . '.'
                . $extension;

            $newImage = $image->storeAs(
                'categories',
                $filename,
                'public'
            );

            $data['image'] = $newImage;
        }


        /*
        |--------------------------------------------------------------------------
        | Remove Existing Image
        |--------------------------------------------------------------------------
        |
        | A newly uploaded image always takes priority over the
        | "remove image" checkbox.
        |
        */

        if (
            !$request->hasFile('image')
            && $request->boolean('remove_image')
        ) {
            $data['image'] = null;
        }


        /*
        |--------------------------------------------------------------------------
        | Do Not Save Checkbox Into Categories Table
        |--------------------------------------------------------------------------
        */

        unset($data['remove_image']);


        /*
        |--------------------------------------------------------------------------
        | Update Database
        |--------------------------------------------------------------------------
        */

        try {

            $category->update($data);

        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Database failed — remove newly uploaded file
            |--------------------------------------------------------------------------
            */

            if ($newImage) {
                Storage::disk('public')->delete(
                    $newImage
                );
            }

            throw $e;
        }


        /*
        |--------------------------------------------------------------------------
        | Delete Old Image After Successful Database Update
        |--------------------------------------------------------------------------
        */

        $shouldDeleteOldImage =
            $oldImage
            && (
                $newImage
                || (
                    !$request->hasFile('image')
                    && $request->boolean('remove_image')
                )
            );

        if ($shouldDeleteOldImage) {
            Storage::disk('public')->delete(
                $oldImage
            );
        }


        return redirect()
            ->route('admin.categories.index')
            ->with(
                'success',
                'Category updated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Category
    |--------------------------------------------------------------------------
    */

    public function destroy(Category $category)
    {
        $image = $category->image;


        /*
        |--------------------------------------------------------------------------
        | Detach Blog Posts
        |--------------------------------------------------------------------------
        */

        $category->posts()->detach();


        /*
        |--------------------------------------------------------------------------
        | Delete Category
        |--------------------------------------------------------------------------
        */

        $category->delete();


        /*
        |--------------------------------------------------------------------------
        | Delete Category Image
        |--------------------------------------------------------------------------
        */

        if ($image) {
            Storage::disk('public')->delete(
                $image
            );
        }


        return redirect()
            ->route('admin.categories.index')
            ->with(
                'success',
                'Category deleted successfully.'
            );
    }
}