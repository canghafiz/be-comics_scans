<?php

namespace App\Http\Controllers;

use App\Http\Resources\SeriesDetailResource;
use App\Services\CacheService;
use Illuminate\Http\Exceptions\HttpResponseException;

class CacheController extends Controller
{
    private CacheService $cacheService;

    public function __construct(CacheService $service)
    {
        $this->cacheService = $service;
    }

    public function clearAllCache(): void
    {
        $this->cacheService->clearAllCache();

        throw new HttpResponseException(response([
            "success" => true,
            "data" => "Success clear cache",
        ], 200));
    }
}
