<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends AdminController
{
    public function index(Request $request): View
    {
        $reviews = Review::query()
            ->with([
                'product',
                'user',
            ])
            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search = trim($request->string('search')->toString());

                    $query->where(function ($innerQuery) use ($search) {
                        $innerQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('title', 'like', "%{$search}%")
                            ->orWhere('review', 'like', "%{$search}%")
                            ->orWhereHas(
                                'product',
                                fn ($productQuery) => $productQuery->where(
                                    'title',
                                    'like',
                                    "%{$search}%"
                                )
                            );
                    });
                }
            )
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where(
                    'status',
                    $request->status
                )
            )
            ->when(
                $request->filled('rating'),
                fn ($query) => $query->where(
                    'rating',
                    $request->rating
                )
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.reviews.index', compact('reviews'));
    }

    public function update(Request $request, Review $review): RedirectResponse
    {
        $validated = $request->validate([
            'status' => [
                'required',
                'in:pending,approved,rejected',
            ],
        ]);

        $review->update($validated);

        return back()->with(
            'success',
            'Review status updated successfully.'
        );
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        return back()->with(
            'success',
            'Review deleted successfully.'
        );
    }
}