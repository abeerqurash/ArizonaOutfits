<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    /**
     * Store a review from either a guest or logged-in user.
     */
    public function store(
        Request $request,
        Product $product
    ): RedirectResponse {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => [
                Rule::requiredIf(!$user),
                'nullable',
                'string',
                'max:255',
            ],

            'email' => [
                Rule::requiredIf(!$user),
                'nullable',
                'email',
                'max:255',
            ],

            'rating' => [
                'required',
                'integer',
                'between:1,5',
            ],

            'title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'review' => [
                'required',
                'string',
                'min:10',
                'max:5000',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Prevent duplicate reviews from logged-in users
        |--------------------------------------------------------------------------
        |
        | A logged-in customer can submit only one review per product.
        |
        */

        if ($user) {
            $existingReview = Review::query()
                ->where('product_id', $product->id)
                ->where('user_id', $user->id)
                ->exists();

            if ($existingReview) {
                return back()
                    ->withInput()
                    ->with(
                        'review_error',
                        'You have already reviewed this product.'
                    );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent repeated guest submissions
        |--------------------------------------------------------------------------
        |
        | Guests are checked using their submitted email address.
        |
        */

        if (!$user) {
            $guestEmail = strtolower(
                trim($validated['email'])
            );

            $existingGuestReview = Review::query()
                ->where('product_id', $product->id)
                ->whereNull('user_id')
                ->whereRaw(
                    'LOWER(email) = ?',
                    [$guestEmail]
                )
                ->exists();

            if ($existingGuestReview) {
                return back()
                    ->withInput()
                    ->with(
                        'review_error',
                        'A review has already been submitted for this product using this email address.'
                    );
            }
        }

        Review::create([
            'product_id' => $product->id,

            'user_id' => $user?->id,

            'name' => $user
                ? $user->name
                : trim($validated['name']),

            'email' => $user
                ? $user->email
                : strtolower(
                    trim($validated['email'])
                ),

            'rating' => (int) $validated['rating'],

            'title' => !empty($validated['title'])
                ? trim($validated['title'])
                : null,

            'review' => trim($validated['review']),

            /*
             * Every new review must be approved by the admin.
             */
            'status' => 'pending',
        ]);

        return redirect()
            ->to(
                route(
                    'products.show',
                    $product->slug
                ) . '#customer-reviews'
            )
            ->with(
                'review_success',
                'Thank you. Your review has been submitted and is awaiting approval.'
            );
    }
}