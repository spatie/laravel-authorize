<?php

namespace Spatie\Authorize\Middleware;

use Closure;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Authorize
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param string                   $ability
     * @param string                   $boundModelName
     *
     * @return mixed
     *
     * @throws HttpException
     */
    public function handle($request, Closure $next, $ability, $boundModelName = null)
    {
        $model = $this->getModelFromRequest($request, $boundModelName);

        if (! $this->hasRequiredAbility($request->user(), $ability, $model)) {
            return $this->handleUnauthorizedRequest($request, $ability, $model);
        }

        return $next($request);
    }

    /**
     * Handle the unauthorized request.
     *
     * @param $request
     * @param string$ability
     * @param null|\Illuminate\Database\Eloquent\Model $model
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @throws HttpException
     */
    protected function handleUnauthorizedRequest($request, $ability, $model)
    {
        if ($request->ajax()) {
            return response('Unauthorized.', 401);
        }

        if (!$request->user()) {
            return redirect()->guest('auth/login');
        }

        throw new HttpException(401, 'This action is unauthorized.');
    }

    /**
     * Determine if the currently logged in use has the given ability.
     *
     * @param $user
     * @param string                                   $ability
     * @param null|\Illuminate\Database\Eloquent\Model $model
     *
     * @return bool
     */
    protected function hasRequiredAbility($user, $ability, $model = null)
    {
        if (! $user) {
            return false;
        }

        if (is_null($model)) {
            return $user->can($ability);
        }

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
