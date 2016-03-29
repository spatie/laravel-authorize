<?php

namespace Spatie\Authorize\Test;

use File;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Routing\Router;
use Orchestra\Testbench\TestCase as Orchestra;
use Route;
use Spatie\Authorize\Middleware\Authorize;
use Spatie\Authorize\Test\Models\Article;
use Spatie\Authorize\Test\Models\User;

abstract class TestCase extends Orchestra
{
    public function setUp()
    {
        parent::setUp();

        $this->setUpDatabase($this->app);

        $this->registerMiddleWare();

        $this->setUpRoutes($this->app);

        $this->setUpGate();
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => $this->getTempDirectory().'/database.sqlite',
            'prefix' => '',
        ]);

        $app['config']->set('app.key', '6rE9Nz59bGRbeMATftriyQjrpF7DcOQm');
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        file_put_contents($this->getTempDirectory().'/database.sqlite', null);

        $app['db']->connection()->getSchemaBuilder()->create('articles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });

        $app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
        });

        foreach (range(1, 2) as $index) {
            User::create([
                'email' => "user{$index}@spatie.be",
            ]);
        }

        foreach (range(1, 2) as $index) {
            Article::create(['name' => "article {$index}"]);
        }
    }

    protected function registerMiddleware()
    {
        $this->app[Router::class]->middleware('can', Authorize::class);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpRoutes($app)
    {
        Route::any('/only-for-logged-in-users', ['middleware' => 'can', function () {
            return 'content for logged in users';
        }]);

        Route::any('/must-have-ability-to-view-top-secret-route', ['middleware' => 'can:viewTopSecretPage', function () {
            return 'content of top secret page';
        }]);

        Route::model('article', Article::class);
        Route::any('/article/{article}', ['middleware' => 'can:viewArticle,article', function ($article) {
            return "article {$article->id}";
        }]);

        Route::model('article2', Article::class);
        Route::any('/articles/{article}/{article2}', ['middleware' => 'can:viewArticles,article,article2', function ($article, $article2) {
            return "article {$article->id} and article2 {$article2->id}";
        }]);

        Route::any('/auth/login', function () {
            return 'login page';
        });
    }

    private function setUpGate()
    {
        $this->app->make(Gate::class)->define('viewTopSecretPage', function ($user) {
            return $user->id == 1;
        });
    }

    public function getTempDirectory($suffix = '')
    {
        return __DIR__.'/temp'.($suffix == '' ? '' : '/'.$suffix);
    }

    protected function initializeDirectory($directory)
    {
        if (File::isDirectory($directory)) {
            File::deleteDirectory($directory);
        }
        File::makeDirectory($directory);
    }

    /**
     * @param string $method
     * @param string $uri
     *
     * @return \Illuminate\Http\Response
     */
    protected function callJson($method, $uri)
    {
        $kernel = $this->app->make('Illuminate\Contracts\Http\Kernel');

        $request = \Request::create($this->prepareUrlForRequest($uri), $method);
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = $kernel->handle($request);

        $kernel->terminate($request, $response);

        return $response;
    }
}
