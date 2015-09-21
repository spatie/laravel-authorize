<?php

namespace Spatie\Authorize;

use Illuminate\Database\Eloquent\Model;

interface UnauthorizedRequestHandler
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
    public function getResponse($request, $ability, Model $model = null);
}
