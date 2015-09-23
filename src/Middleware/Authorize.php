<?php

namespace Spatie\Authorize\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Authorize
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param string|null              $ability
     * @param string|null              $boundModelName
     *
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function handle($request, Closure $next, $ability = null, $boundModelName = null)
    {
        $model = $this->getModelFromRequest($request, $boundModelName);

        if (!$this->hasRequiredAbility($request->user(), $ability, $model)) {
            return $this->handleUnauthorizedRequest($request, $ability, $model);
        }

        return $next($request);
    }

    /**
     * Get the model from the request using given boundModelName.
     *
     * @param mixed  $request
     * @param string $boundModelName
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    protected function getModelFromRequest($request, $boundModelName)
    {
        if (is_null($boundModelName)) {
            return;
        }

        return $request->route($boundModelName);
    }

    /**
     * Determine if the currently logged in use has the given ability.
     *
     * @param $user
     * @param string|null                              $ability
     * @param \Illuminate\Database\Eloquent\Model|null $model
     *
     * @return bool
     */
    protected function hasRequiredAbility($user, $ability = null, $model = null)
    {
        if (!$user) {
            return false;
        }

        if (is_null($ability)) {
            return true;
        }

        /*
         * Some gates may check on number of arguments given. If model
         * is null, don't pass it as an argument.
         */
        if (is_null($model)) {
            return $user->can($ability);
        }

        return $user->can($ability, $model);
    }

    /**
     * Handle the unauthorized request.
     *
     * @param $request
     * @param string|null                              $ability
     * @param \Illuminate\Database\Eloquent\Model|null $model
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\RedirectResponse|Response
     *
     * @throws HttpException
     */
    protected function handleUnauthorizedRequest($request, $ability = null, $model = null)
    {
        if ($request->ajax()) {
            return response('Unauthorized.', Response::HTTP_UNAUTHORIZED);
        }

        if (!$request->user()) {
            return redirect()->guest('auth/login');
        }

        throw new HttpException(Response::HTTP_UNAUTHORIZED, 'This action is unauthorized.');
    }
}
