<?php

namespace App\Http\Controllers;

use App\Http\Resources\LatestCollection;
use App\Http\Resources\ProjectAllCollection;
use App\Services\MangaService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class MangaController extends Controller
{
    private MangaService $mangaService;

    public function __construct(MangaService $service)
    {
        $this->mangaService = $service;
    }

    public function latest(Request $request) :void {
        $limit = $request->input('limit', 20);
        $clearCache = $request->input('clear-cache', false);

        $projects = $this->mangaService->latest($limit, $clearCache);
        throw new HttpResponseException(response([
            "success" => true,
            "data" => new LatestCollection($projects)
        ], 200));
    }

    public function projectAll(Request $request) :void {
        $limit = $request->input('limit', 20);
        $clearCache = $request->input('clear-cache', false);

        $projects = $this->mangaService->projectAll($limit, $clearCache);
        throw new HttpResponseException(response([
            "success" => true,
            "data" => new ProjectAllCollection($projects)
        ], 200));
    }
}
