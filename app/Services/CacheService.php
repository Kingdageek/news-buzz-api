<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

class CacheService
{
    public static function getUserFeed(int $user_id, bool $hasUserPreferences)
    {
        $key = self::getUserFeedKey($user_id, $hasUserPreferences);
        return json_decode(Redis::get($key), true);
    }

    public static function setUserFeed(int $user_id, array $feed, bool $hasUserPreferences)
    {
        $key = self::getUserFeedKey($user_id, $hasUserPreferences);
        Redis::set($key, json_encode($feed));
    }

    private static function getUserFeedKey(int $user_id, bool $hasUserPreferences): string
    {
        $todaysDate = date("Y-m-d");
        // general feed key: "feed_$todaysDate"
        $key = "feed_" . $todaysDate;
        // if user has preferences. check for cache hit
        // get the user's feed for that day
        // if no preferences, get the general feed for the day
        if ($hasUserPreferences) {
            // $key="feed_$todaysDate_$user_id"
            $key = $key . "_$user_id";
        }
        return $key;
    }

    public static function deleteUserFeed(int $user_id)
    {
        $todaysDate = date('Y-m-d');
        // invalidate on this user's feed in cache
        $key = "feed_" . $todaysDate . "_$user_id";
        $cachedFeed = Redis::get($key);
        if (isset($cachedFeed)) {
            Redis::del($key);
        }
    }
}
