<?php

namespace App\Http\Controllers;

use App\Http\Resources\MovieResource;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MovieController extends Controller
{
    protected $baseUrl;

    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('tmdb.base_url');
        $this->apiKey = config('tmdb.api_key');
    }

    public function index(Request $request, string $type): JsonResponse
    {
        $page = $request->query('page', 1);

        $allowedTypes = ['popular', 'top_rated', 'upcoming', 'now_playing'];

        if (! in_array($type, $allowedTypes)) {
            Log::warning('Invalid movie type requested', ['type' => $type]);

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid movie type. Allowed types: '.implode(', ', $allowedTypes),
            ], 400);
        }

        try {
            Log::info('Fetching movies from TMDB', [
                'type' => $type,
                'page' => $page,
            ]);

            $url = "{$this->baseUrl}/{$type}";
            
            $httpResponse = Http::withoutVerifying()
                ->timeout(30)
                ->get($url, [
                    'api_key' => $this->apiKey,
                    'page' => $page,
                ]);

            $statusCode = $httpResponse->status();
            $responseData = $httpResponse->json();

            if ($statusCode >= 200 && $statusCode < 300) {
                Log::info('Movies fetched successfully from TMDB', [
                    'type' => $type,
                    'page' => $page,
                    'total_results' => $responseData['total_results'] ?? 0,
                ]);

                return response()->json([
                    'status' => 'success',
                    'data' => MovieResource::collection($responseData['results']),
                ]);
            }

            Log::error('TMDB API returned error', [
                'type' => $type,
                'status_code' => $statusCode,
                'response' => $responseData,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch movies from TMDB',
                'tmdb_error' => $responseData,
            ], $statusCode);

        } catch (Exception $e) {
            Log::error('Failed to connect to TMDB API', [
                'type' => $type,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to connect to TMDB API',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            Log::info('Fetching movie details from TMDB', ['movie_id' => $id]);

            $url = "{$this->baseUrl}/{$id}";
            
            $httpResponse = Http::withoutVerifying()
                ->timeout(30)
                ->get($url, [
                    'api_key' => $this->apiKey,
                ]);

            $statusCode = $httpResponse->status();
            $responseData = $httpResponse->json();

            if ($statusCode >= 200 && $statusCode < 300) {
                Log::info('Movie details fetched successfully', [
                    'movie_id' => $id,
                    'title' => $responseData['title'] ?? 'Unknown',
                ]);

                return response()->json([
                    'status' => 'success',
                    'data' =>new MovieResource($responseData),
                ]);
            }

            Log::warning('Movie not found in TMDB', [
                'movie_id' => $id,
                'status_code' => $statusCode,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Movie not found',
                'tmdb_error' => $responseData,
            ], $statusCode);

        } catch (Exception $e) {
            Log::error('Failed to fetch movie details', [
                'movie_id' => $id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to connect to TMDB API',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
