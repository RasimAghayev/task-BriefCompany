<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * The number of rows a listing holds.
 *
 * The rule is the same for every listing of the API, so it lives in one
 * place instead of twice in the controllers.
 */
class PerPage
{
    /**
     * The number of rows a page holds when the request does not say.
     */
    public const DEFAULT = 15;

    /**
     * The largest number of rows a request may ask for.
     */
    public const MAX = 100;

    /**
     * The number of rows the request asks for.
     *
     * A request that asks for nothing, for a number that is not a number or
     * for a number below one gets the default. A request that asks for more
     * than the largest number gets the largest number.
     */
    public static function from(Request $request): int
    {
        $perPage = (int) $request->query('per_page', self::DEFAULT);

        if ($perPage < 1) {
            return self::DEFAULT;
        }

        return min($perPage, self::MAX);
    }
}
