<?php

return [

    /*
     * Here you can specify which class should handle the unauthorized request. You
     * can use the default one or specify any class that implements
     * the UnauthorizedRequestHandler-interface.
     */
    'unauthorizedRequestHandler' => Spatie\Authorize\UnauthorizedRequestHandlers\DefaultUnauthorizedRequestHandler::class,

];