<?php

namespace App\Http\Controllers;

use App\Http\Resources\MovieResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MovieController extends Controller
{
    protected $baseUrl;

    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.tmdb.base_url');
        $this->apiKey  = config('services.tmdb.api_key');
    }

    public function index(Request $request, string $type): JsonResponse
    {
        $page = $request->query('page', 1);

        $allowedTypes = ['popular', 'top_rated'];

        if (! in_array($type, $allowedTypes)) {

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid movie type. Allowed types: '.implode(', ', $allowedTypes),
            ], 400);
        }

        $url = "{$this->baseUrl}/movie/{$type}";

        $httpResponse = Http::withoutVerifying()
            ->timeout(30)
            ->get($url, [
                'api_key' => $this->apiKey,
                'page' => $page,
            ]);

        $statusCode = $httpResponse->status();
        $responseData = $httpResponse->json();

        if ($statusCode >= 200 && $statusCode < 300) {

            return response()->json([
                'status' => 'success',
                'data' => MovieResource::collection($responseData['results']),
                'current_page' => $responseData['page'],
                'total_pages' => $responseData['total_pages'],
                'total_results' => $responseData['total_results'],
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to fetch movies from TMDB',
            'tmdb_error' => $responseData,
        ], $statusCode);

    }

    public function show(string $id): JsonResponse
    {

        $url = "{$this->baseUrl}/movie/{$id}";

        $httpResponse = Http::withoutVerifying()
            ->timeout(30)
            ->get($url, [
                'api_key' => $this->apiKey,
            ]);

        $statusCode = $httpResponse->status();
        $responseData = $httpResponse->json();

        if ($statusCode >= 200 && $statusCode < 300) {

            return response()->json([
                'status' => 'success',
                'data' => new MovieResource($responseData),
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Movie not found',
            'tmdb_error' => $responseData,
        ], $statusCode);

    }

    public function search(Request $request): JsonResponse
    {
        $responseData = Http::withoutVerifying()
            ->timeout(30)
            ->get($this->baseUrl.'/search/movie', [
                'api_key' => $this->apiKey,
                'query' => $request->q,
            ]);


        return response()->json([
            'status' => 'success',
            'data' => MovieResource::collection($responseData['results']),
            'current_page' => $responseData['page'],
            'total_pages' => $responseData['total_pages'],
            'total_results' => $responseData['total_results'],
        ]);
    }
}
