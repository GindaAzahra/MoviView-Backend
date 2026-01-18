<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReviewController extends Controller
{
    /**
     * Create a new review for a movie
     */
    public function store(ReviewRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        $data = $request->validated();

        $existingReview = Review::where('id_user', $user->id_user)
            ->where('id_movie', $data['id_movie'])
            ->first();

        if ($existingReview) {
            return response()->json([
                'message' => 'You have already reviewed this movie',
            ], 409);
        }

        $review = Review::create([
            'id_review' => (string) Str::uuid(),
            'id_user' => $user->id_user,
            'id_movie' => $data['id_movie'],
            'rating' => $data['rating'],
            'review' => $data['review'],
        ]);

        $review->load('user');

        return response()->json([
            'status' => 'success',
            'data' => new ReviewResource($review),
        ], 201);

    }

    /**
     * Get all reviews for a specific movie
     */
    public function getByMovie(string $movieId): JsonResponse
    {
        Log::info('Fetching reviews for movie', ['movie_id' => $movieId]);

        $reviews = Review::where('id_movie', $movieId)
            ->with('user')
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => ReviewResource::collection($reviews),
        ]);
    }

    /**
     * Get a single review by ID
     */
    public function show(string $id): JsonResponse
    {

        $review = Review::with('user')->find($id);

        if (! $review) {
            Log::warning('Review not found', ['review_id' => $id]);

            return response()->json([
                'message' => 'Review not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => new ReviewResource($review),
        ]);
    }

    /**
     * Update an existing review
     */
    public function update(ReviewRequest $request, string $id): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        $data = $request->validated();

        $review = Review::find($id);

        if (! $review) {
            return response()->json([
                'message' => 'Review not found',
            ], 404);
        }
        if ($review->id_user !== $user->id_user) {
            Log::warning('Unauthorized review update attempt', [
                'review_id' => $id,
                'review_owner_id' => $review->id_user,
                'attempted_by_user_id' => $user->id_user,
            ]);

            return response()->json([
                'message' => 'Unauthorized to update this review',
            ], 403);
        }

        $oldRating = $review->rating;
        $review->update([
            'rating' => $data['rating'],
            'review' => $data['review'],
        ]);

        $review->load('user');

        return response()->json([
            'status' => 'success',
            'data' => new ReviewResource($review),
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        $review = Review::find($id);

        if (! $review) {
            Log::warning('Review not found for deletion', ['review_id' => $id]);

            return response()->json([
                'message' => 'Review not found',
            ], 404);
        }

        if ($review->id_user !== $user->id_user) {

            return response()->json([
                'message' => 'Unauthorized to delete this review',
            ], 403);
        }

        $movieId = $review->id_movie;
        $review->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Review deleted successfully',
        ]);
    }

    /**
     * Get all reviews by the authenticated user
     */
    public function myReviews(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        $reviews = Review::where('id_user', $user->id_user)
            ->with('user')
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => ReviewResource::collection($reviews),
        ]);
    }
}
