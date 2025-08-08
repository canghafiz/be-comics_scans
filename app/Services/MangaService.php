<?php

namespace App\Services;

interface MangaService
{
    public function latest(int $limit, bool $clearCache) :array;
    public function projectAll(int $limit, bool $clearCache) :array;
}
