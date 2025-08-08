<?php

namespace App\Services\Impl;

use App\Models\WpPosts;
use App\Models\WpTermRelationships;
use App\Models\WpTerms;
use App\Services\MangaService;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class MangaServiceImpl implements MangaService
{
    public function latest(int $limit = 20, bool $clearCache = false): array
    {
        $cacheKey = "latest:manga:limit:{$limit}";

        if ($clearCache) {
            $this->forgetCacheValue($cacheKey);
            $this->clearCoverCaches();

            $freshData = $this->fetchLatestManga($limit, $clearCache);
            $this->setCacheValue($cacheKey, $freshData);

            return $freshData;
        }

        return $this->getCacheValue($cacheKey, function () use ($limit) {
            return $this->fetchLatestManga($limit, false);
        });
    }

    public function projectAll(int $limit = 20, bool $clearCache = false): array
    {
        $cacheKey = "project:all:limit:{$limit}";

        if ($clearCache) {
            $this->forgetCacheValue($cacheKey);
            $this->clearCoverCaches();

            $freshData = $this->fetchProjectAll($limit, $clearCache);
            $this->setCacheValue($cacheKey, $freshData);

            return $freshData;
        }

        return $this->getCacheValue($cacheKey, function () use ($limit) {
            return $this->fetchProjectAll($limit, false);
        });
    }

    // Helper Function
    private function fetchLatestManga(int $limit, bool $clearChildCache): array
    {
        $posts = WpPosts::where('post_type', 'manga')
            ->with('meta')
            ->whereHas('meta', function ($query) {
                $query->where('meta_key', 'ero_project')
                    ->where('meta_value', 0);
            })
            ->orderBy('post_modified', 'desc')
            ->limit($limit)
            ->get();

        return $posts->map(function ($post) use ($clearChildCache) {
            $coverUrl = $this->getCover($post->ID, $clearChildCache);
            $post['cover'] = $coverUrl;
            $post['chapters'] = $this->getChaptersBySlug($post->post_name, $clearChildCache);
            return $post;
        })->toArray();
    }

    private function fetchProjectAll(int $limit, bool $clearChildCache): array
    {
        $posts = WpPosts::where('post_type', 'manga')
            ->with('meta')
            ->whereHas('meta', function ($query) {
                $query->where('meta_key', 'ero_project')
                    ->where('meta_value', 1);
            })
            ->orderBy('post_modified', 'desc')
            ->limit($limit)
            ->get();

        return $posts->map(function ($post) use ($clearChildCache) {
            $coverUrl = $this->getCover($post->ID, $clearChildCache);
            $post['cover'] = $coverUrl;
            $post['chapters'] = $this->getChaptersBySlug($post->post_name, $clearChildCache);
            return $post;
        })->toArray();
    }
    private function getChaptersBySlug(string $slug, bool $clearCache): array
    {
        $cacheKey = "chapters:slug:{$slug}";

        if ($clearCache) {
            $this->forgetCacheValue($cacheKey);

            $freshChapters = $this->fetchChaptersBySlugData($slug);
            $this->setCacheValue($cacheKey, $freshChapters);

            return $freshChapters;
        }

        return $this->getCacheValue($cacheKey, function () use ($slug) {
            return $this->fetchChaptersBySlugData($slug);
        });
    }

    private function fetchChaptersBySlugData(string $slug): array
    {
        $term = WpTerms::where('slug', $slug)->first();

        if (!$term) {
            return [];
        }

        $termRelationships = WpTermRelationships::whereHas('termTaxonomy', function ($query) use ($term) {
            $query->where('term_id', $term->term_id);
        })->orderBy('object_id', 'desc')->get();

        return $termRelationships->map(function ($relationship, $index) use ($termRelationships) {
            $chapterData = $this->getSingleChapterById($relationship->object_id);

            if ($chapterData) {
                $chapterData['chapterNum'] = count($termRelationships) - $index;
                return $chapterData;
            }

            return null;
        })->filter()->values()->toArray();
    }

    private function getSingleChapterById(int $id): ?array
    {
        $post = WpPosts::where('id', $id)->first();

        if ($post) {
            return [
                'chapters' => $post->post_content,
                'time' => $post->post_date,
            ];
        }

        return null;
    }

    private function getCover(int $idPostParent, bool $clearCache): ?string
    {
        $cacheKey = "cover:{$idPostParent}";

        if ($clearCache) {
            $this->forgetCacheValue($cacheKey);

            $freshCover = $this->fetchCoverData($idPostParent);
            $this->setCacheValue($cacheKey, $freshCover);

            return $freshCover;
        }

        return $this->getCacheValue($cacheKey, function () use ($idPostParent) {
            return $this->fetchCoverData($idPostParent);
        });
    }

    private function fetchCoverData(int $idPostParent): ?string
    {
        $post = WpPosts::where('post_parent', $idPostParent)
            ->first();

        return $post ? $post->guid : null;
    }

    private function isRedisAvailable(): bool
    {
        try {
            Cache::store('redis')->get('test_connection');
            return true;
        } catch (Exception $e) {
            Log::warning('Redis connection failed: ' . $e->getMessage());
            return false;
        }
    }

    private function getCacheValue(string $key, callable $callback)
    {
        if (!$this->isRedisAvailable()) {
            return $callback();
        }

        try {
            return Cache::store('redis')->remember($key, 3600, $callback); // 1 hour = 3600 seconds
        } catch (Exception $e) {
            Log::warning("Redis cache operation failed for key {$key}: " . $e->getMessage());
            return $callback();
        }
    }

    private function setCacheValue(string $key, $value): void
    {
        if (!$this->isRedisAvailable()) {
            return;
        }

        try {
            Cache::store('redis')->put($key, $value, 3600); // 1 hour = 3600 seconds
        } catch (Exception $e) {
            Log::warning("Failed to set cache for key {$key}: " . $e->getMessage());
        }
    }

    private function forgetCacheValue(string $key): void
    {
        if (!$this->isRedisAvailable()) {
            return;
        }

        try {
            Cache::store('redis')->forget($key);
        } catch (Exception $e) {
            Log::warning("Failed to forget cache for key {$key}: " . $e->getMessage());
        }
    }

    private function clearCoverCaches(): void
    {
        if (!$this->isRedisAvailable()) {
            Log::info('Redis not available, skipping cover cache clear');
            return;
        }

        try {
            $keys = Redis::keys('cover:*');

            if (!empty($keys)) {
                Redis::del($keys);
            }
        } catch (Exception $e) {
            Log::warning('Failed to clear cover caches: ' . $e->getMessage());
        }
    }
}
