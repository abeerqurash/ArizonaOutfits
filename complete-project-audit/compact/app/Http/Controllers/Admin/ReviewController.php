<?php
namespace App\Http\Controllers\Admin;
use App\Models\Review;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
class ReviewController extends AdminController
{
    public function index(Request $request): View
    {
        $reviews = Review::query()
            ->with([
                'product.images',
                'user',
            ])
            ->when(
                $request->filled('search'),
                function (Builder $query) use ($request): void {
                    $search = trim($request->string('search')->toString());
                    $query->where(function (Builder $innerQuery) use ($search): void {
                        $innerQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('title', 'like', "%{$search}%")
                            ->orWhere('review', 'like', "%{$search}%")
                            ->orWhereHas(
                                'product',
                                fn (Builder $productQuery) => $productQuery
                                    ->where('title', 'like', "%{$search}%")
                            );
                    });
                }
            )
            ->when(
                $request->filled('status'),
                fn (Builder $query) => $query->where(
                    'status',
                    $request->string('status')->toString()
                )
            )
            ->when(
                $request->filled('rating'),
                fn (Builder $query) => $query->where(
                    'rating',
                    (int) $request->input('rating')
                )
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();
        $reviewStats = [
            'total' => Review::query()->count(),
            'pending' => Review::query()->where('status', 'pending')->count(),
            'approved' => Review::query()->where('status', 'approved')->count(),
            'rejected' => Review::query()->where('status', 'rejected')->count(),
        ];
        return view(
            'admin.reviews.index',
            compact('reviews', 'reviewStats')
        );
    }
    public function update(Request $request, Review $review): RedirectResponse
    {
        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in(['pending', 'approved', 'rejected']),
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