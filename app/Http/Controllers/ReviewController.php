<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\ReviewsExport;
use Illuminate\Support\Facades\Http;

class ReviewController extends Controller
{
    /**
     * Get all reviews with pagination
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 10);
        $perPage = $perPage > 0 ? $perPage : 10;

        Log::info('Fetching paginated reviews', ['per_page' => $perPage]);

        $reviews = Review::with('user')
            ->latest()
            ->paginate($perPage);

        Log::info('Paginated reviews retrieved successfully', [
            'per_page' => $perPage,
            'count' => $reviews->count(),
            'current_page' => $reviews->currentPage(),
        ]);

        return response()->json([
            'status' => 'success',
            'data' => ReviewResource::collection($reviews),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
                'last_page' => $reviews->lastPage(),
            ],
        ]);
    }

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

        Log::info('Reviews retrieved successfully', [
            'movie_id' => $movieId,
            'count' => $reviews->count(),
        ]);

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
        Log::info('Fetching single review', ['review_id' => $id]);

        $review = Review::with('user')->find($id);

        if (! $review) {
            Log::warning('Review not found', ['review_id' => $id]);

            return response()->json([
                'message' => 'Review not found',
            ], 404);
        }

        Log::info('Review retrieved successfully', [
            'review_id' => $id,
            'movie_id' => $review->id_movie,
        ]);

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

        Log::info('Attempting to update review', [
            'review_id' => $id,
            'user_id' => $user->id_user,
            'new_rating' => $data['rating'],
        ]);

        $review = Review::find($id);

        if (! $review) {
            Log::warning('Review not found for update', ['review_id' => $id]);

            return response()->json([
                'message' => 'Review not found',
            ], 404);
        }

        // Check if the authenticated user owns this review
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

        Log::info('Review updated successfully', [
            'review_id' => $id,
            'user_id' => $user->id_user,
            'old_rating' => $oldRating,
            'new_rating' => $data['rating'],
        ]);

        return response()->json([
            'status' => 'success',
            'data' => new ReviewResource($review),
        ]);
    }

    /**
     * Delete a review
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        Log::info('Attempting to delete review', [
            'review_id' => $id,
            'user_id' => $user->id_user,
        ]);

        $review = Review::find($id);

        if (! $review) {
            Log::warning('Review not found for deletion', ['review_id' => $id]);

            return response()->json([
                'message' => 'Review not found',
            ], 404);
        }

        // Check if the authenticated user owns this review
        if ($review->id_user !== $user->id_user) {
            Log::warning('Unauthorized review deletion attempt', [
                'review_id' => $id,
                'review_owner_id' => $review->id_user,
                'attempted_by_user_id' => $user->id_user,
            ]);

            return response()->json([
                'message' => 'Unauthorized to delete this review',
            ], 403);
        }

        $movieId = $review->id_movie;
        $review->delete();

        Log::info('Review deleted successfully', [
            'review_id' => $id,
            'user_id' => $user->id_user,
            'movie_id' => $movieId,
        ]);

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

        Log::info('Fetching user reviews', ['user_id' => $user->id_user]);

        $reviews = Review::where('id_user', $user->id_user)
            ->with('user')
            ->latest()
            ->get();

        Log::info('User reviews retrieved successfully', [
            'user_id' => $user->id_user,
            'count' => $reviews->count(),
        ]);

        return response()->json([
            'status' => 'success',
            'data' => ReviewResource::collection($reviews),
        ]);
    }

    /**
     * Export reviews as Excel (xlsx) or PDF.
     *
     * Optional filters via query params:
     * - id_movie
     * - user_id
     * - rating
     * - date_from (Y-m-d)
     * - date_to (Y-m-d)
     */
    public function export(Request $request, string $type)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        $type = strtolower($type);

        if (! in_array($type, ['excel', 'pdf'], true)) {
            return response()->json([
                'message' => 'Unsupported export type. Allowed: excel, pdf',
            ], 422);
        }

        Log::info('Exporting reviews', [
            'export_type' => $type,
            'requested_by' => $user->id_user,
            'filters' => $request->query(),
        ]);

        $query = Review::with('user');

        // Optional filters
        if ($request->filled('id_movie')) {
            $query->where('id_movie', $request->query('id_movie'));
        }

        // Backwards compatibility: still support movie_id if provided
        if ($request->filled('movie_id')) {
            $query->where('id_movie', $request->query('movie_id'));
        }

        if ($request->filled('user_id')) {
            $query->where('id_user', $request->query('user_id'));
        }

        if ($request->filled('rating')) {
            $query->where('rating', $request->query('rating'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->query('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->query('date_to'));
        }

        $reviews = $query->latest()->get();

        // Build movie details map: [id_movie => 'Title (Year)']
        $movieIds = $reviews->pluck('id_movie')->filter()->unique()->values();
        $movieDetails = collect();
        if ($movieIds->isNotEmpty()) {
            $baseUrl = config('tmdb.base_url');
            $apiKey  = config('tmdb.api_key');

            foreach ($movieIds as $movieId) {
                try {
                    $url = "{$baseUrl}/{$movieId}";
                    $resp = Http::withoutVerifying()
                        ->timeout(10)
                        ->get($url, ['api_key' => $apiKey]);

                    if ($resp->successful()) {
                        $data  = $resp->json();
                        $title = $data['title'] ?? 'Unknown title';
                        $year  = null;

                        if (! empty($data['release_date'])) {
                            $year = substr($data['release_date'], 0, 4);
                        }

                        $label = $year ? "{$title} ({$year})" : $title;
                        $movieDetails->put($movieId, $label);
                    } else {
                        $movieDetails->put($movieId, $movieId);
                    }
                } catch (\Throwable $e) {
                    Log::warning('Failed to fetch movie detail for export', [
                        'movie_id' => $movieId,
                        'error' => $e->getMessage(),
                    ]);
                    $movieDetails->put($movieId, $movieId);
                }
            }
        }

        if ($type === 'excel') {
            // Pass movie details mapping into export
            return Excel::download(
                new ReviewsExport($reviews, $movieDetails),
                'reviews_export.xlsx'
            );
        }

        // Use barryvdh/laravel-dompdf to generate PDF from the Blade view
        $pdf = Pdf::loadView('exports.reviews-pdf', [
            'reviews'      => $reviews,
            'exportedBy'   => $user,
            'generatedAt'  => now(),
            'movieDetails' => $movieDetails,
        ]);

        return $pdf->download('reviews_export.pdf');
    }
}
