<?php

namespace Spatie\Authorize\Test;

use Illuminate\Contracts\Auth\Access\Gate;
use Spatie\Authorize\Exceptions\UnauthorizedRequest;
use Spatie\Authorize\Test\Models\User;

class MiddlewareTest extends TestCase
{
    protected $unauthorizedUserId = 2;
    protected $authorizedUserId = 1;

    /**
     * @test
     */
    public function it_redirects_unauthorized_requests_from_guests_to_the_login_page()
    {
        $this->call('GET', '/protected-route');

        $this->assertRedirectedTo('auth/login');
    }

    /**
     * @test
     */
    public function it_protects_routes_from_unauthorized_users()
    {
        auth()->login(User::find($this->unauthorizedUserId));

        $this->setExpectedException(UnauthorizedRequest::class);

        $this->call('GET', '/protected-route');
    }

    /**
     * @test
     */
    public function it_allows_authorized_users_to_View_protected_routes()
    {
        auth()->login(User::find($this->authorizedUserId));

        $response = $this->call('GET', '/protected-route');

        $this->assertEquals('content of protected route', $response->getContent());
    }

    /**
     * @test
     */
    public function it_sends_a_401_reponse_for_authorized_json_requests()
    {
        auth()->login(User::find($this->unauthorizedUserId));

        $response = $this->callJson('GET', '/protected-route');

        $this->assertEquals(401, $response->getStatusCode());

        $this->assertEquals('Unauthorized.', $response->getContent());
    }

    /**
     * @test
     */
    public function it_can_use_the_models_from_a_route_that_uses_route_model_binding()
    {
        $this->app->make(Gate::class)->define('viewArticle', function ($user, $article) {
            return $user->id == $article->id;
        });

        auth()->login(User::find(1));

        $response = $this->call('GET', '/article/1');

        $this->assertEquals("article 1", $response->getContent());

        $this->setExpectedException(UnauthorizedRequest::class);

        $this->call('GET', '/article/2');
    }
}
