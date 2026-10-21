<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * The application has no page to send a visitor to, so an unauthenticated
     * request is answered with the status 401 and never with a redirect.
     *
     * The project has no route named "login" either, so the redirect of the
     * parent class would have ended in an error.
     *
     * @param  Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        return null;
    }
}
