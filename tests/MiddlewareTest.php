<?php

namespace Spatie\Authorize\Test;

use Illuminate\Contracts\Auth\Access\Gate;
use Spatie\Authorize\Test\Models\User;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MiddlewareTest extends TestCase
{
    protected $unauthorizedUserId = 2;
    protected $authorizedUserId = 1;

    /**
     * @test
     */
    public function it_redirects_unauthenticted_users()
    {
        $this->call('GET', '/only-for-logged-in-users');

        $this->assertRedirectedTo(config('laravel-authorize.login_url'));
    }

    /**
     * @test
     */
    public function it_allows_authenticated_users_to_view_protected_routes()
    {
        auth()->login(User::find(1));

        $response = $this->call('GET', '/only-for-logged-in-users');

        $this->assertEquals('content for logged in users', $response->getContent());
    }

    /**
     * @test
     */
    public function it_redirects_unauthorized_guests()
    {
        $this->call('GET', '/must-have-ability-to-view-top-secret-route');

        $this->assertRedirectedTo(config('laravel-authorize.login_url'));
    }

    /**
     * @test
     */
    public function it_redirects_unauthorized_users()
    {
        auth()->login(User::find($this->unauthorizedUserId));

        $this->setExpectedException(HttpException::class);

        $this->call('GET', '/must-have-ability-to-view-top-secret-route');
    }

    /**
     * @test
     */
    public function it_allows_authorized_users_to_view_protected_routes()
    {
        auth()->login(User::find($this->authorizedUserId));

        $response = $this->call('GET', '/must-have-ability-to-view-top-secret-route');

        $this->assertEquals('content of top secret page', $response->getContent());
    }

    /**
     * @test
     */
    public function it_sends_a_401_reponse_for_authorized_json_requests()
    {
        auth()->login(User::find($this->unauthorizedUserId));

        $response = $this->callJson('GET', '/must-have-ability-to-view-top-secret-route');

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

        $this->assertEquals('article 1', $response->getContent());

        $this->setExpectedException(HttpException::class);

        $this->call('GET', '/article/2');
    }
}
