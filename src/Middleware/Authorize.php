<?php

namespace Spatie\Authorize\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Spatie\Authorize\UnauthorizedRequestHandler;

class Authorize
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;
    /**
     * @var UnauthorizedRequestHandler
     */
    private $unauthorizedRequestHandler;

    /**
     * Create a new filter instance.
     *
     * @param Guard                      $auth
     * @param UnauthorizedRequestHandler $unauthorizedRequestHandler
     */
    public function __construct(Guard $auth, UnauthorizedRequestHandler $unauthorizedRequestHandler)
    {
        $this->auth = $auth;
        $this->unauthorizedRequestHandler = $unauthorizedRequestHandler;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param string                   $ability
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $ability, $model = null)
    {
        if (! $this->hasRequiredAbility($ability)) {
            return $this->unauthorizedRequestHandler->getResponse($request, $ability);
        }

        return $next($request);
    }

    /**
     * Determine if the currently logged in use has the given ability.
     *
     * @param string $ability
     *
     * @return bool
     */
    protected function hasRequiredAbility($ability)
    {
        if (! $this->auth->check()) {
            return false;
        }

        return $this->auth->user()->can($ability);
    }
}
