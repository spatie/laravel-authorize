<?php

namespace Spatie\Authorize\UnauthorizedRequestHandlers;
use Illuminate\Database\Eloquent\Model;
use Spatie\Authorize\Exceptions\UnauthorizedRequest;
use Spatie\Authorize\UnauthorizedRequestHandler;

class DefaultUnauthorizedRequestHandler implements UnauthorizedRequestHandler
{
    /**
     * Get the response for the given unauthorized request.
     *
     * @param $request
     * @param string $ability
     * @param Model  $model
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @throws UnauthorizedRequest
     */
    public function getResponse($request, $ability, Model $model = null)
    {
        if ($request->ajax()) {
            return response('Unauthorized.', 401);
        }

        if (! auth()->check()) {
            return redirect()->guest('auth/login');
        }

        throw new UnauthorizedRequest();
    }
}
