<?php

namespace App\Services\Impl;

use App\Services\CacheService;
use Illuminate\Support\Facades\Cache;

class CacheServiceImpl implements CacheService
{

    public function clearAllCache() :void
    {
        Cache::flush();
    }
}
