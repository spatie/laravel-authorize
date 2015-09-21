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
     * @param string                   $boundModelName
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $ability, $boundModelName = null)
    {
        $model = $this->getModelFromRequest($request, $boundModelName);

        if (! $this->hasRequiredAbility($ability, $model)) {
            return $this->unauthorizedRequestHandler->getResponse($request, $ability, $model);
        }

        return $next($request);
    }

    /**
     * Determine if the currently logged in use has the given ability.
     *
     * @param string                                   $ability
     * @param null|\Illuminate\Database\Eloquent\Model $model
     *
     * @return bool
     */
    protected function hasRequiredAbility($ability, $model = null)
    {
        if (! $this->auth->check()) {
            return false;
        }

        return $this->auth->user()->can($ability, $model);
    }

    /**
     * Get the model from the request using given boundModelName.
     *
     * @param $request
     * @param $boundModelName
     *
     * @return null|\Illuminate\Database\Eloquent\Model
     */
    protected function getModelFromRequest($request, $boundModelName)
    {
        if (is_null($boundModelName)) {
            return;
        }

        return $request->route($boundModelName);
    }
}
