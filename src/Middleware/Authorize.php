<?php

namespace Spatie\Authorize\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Spatie\Authorize\UnauthorizedRequestHandler;

class Authorize
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string $ability
     * @param string $boundModelName
     * @return mixed
     * @throws HttpException
     */
    public function handle($request, Closure $next, $ability, $boundModelName = null)
    {
        $model = $this->getModelFromRequest($request, $boundModelName);

        if (! $this->hasRequiredAbility($request->user, $ability, $model)) {

            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            }

            if (! $request->user()) {
                return redirect()->guest('auth/login');
            }

            throw new HttpException(401, 'This action is unauthorized.');
        }

        return $next($request);
    }

    /**
     * Determine if the currently logged in use has the given ability.
     *
     * @param $user
     * @param string $ability
     * @param null|\Illuminate\Database\Eloquent\Model $model
     * @return bool
     */
    protected function hasRequiredAbility($user, $ability, $model = null)
    {
        if (! $user) return false;

        return $user->can($ability, $model);
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
