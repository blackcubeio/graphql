<?php

declare(strict_types=1);

/**
 * GraphqlServiceProvider.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Graphql\Laravel;

use Blackcube\Dcore\Interfaces\JsonLdBuilderInterface;
use Blackcube\Dcore\Services\JsonLdBuilder;
use Blackcube\Graphql\Handlers\Graphql;
use Blackcube\Graphql\Middlewares\LaravelGraphqlMiddleware;
use Illuminate\Support\ServiceProvider;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Laravel service provider for the Blackcube GraphQL endpoint.
 * Registers the Graphql handler and the LaravelGraphqlMiddleware.
 *
 * Configuration via config/blackcube-graphql.php (publish with --tag=blackcube-graphql-config).
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class GraphqlServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/laravel/blackcube-graphql.php', 'blackcube-graphql');

        $graphql = config('blackcube-graphql');

        $this->app->singletonIf(ResponseFactoryInterface::class, \HttpSoft\Message\ResponseFactory::class);
        $this->app->singletonIf(StreamFactoryInterface::class, \HttpSoft\Message\StreamFactory::class);
        $this->app->singletonIf(JsonLdBuilderInterface::class, JsonLdBuilder::class);

        $this->app->singleton(Graphql::class, function () use ($graphql) {
            return new Graphql(
                $this->app->make(ResponseFactoryInterface::class),
                $this->app->make(StreamFactoryInterface::class),
                $this->app->make(JsonLdBuilderInterface::class),
                debug: (bool) ($graphql['debug'] ?? false),
                fastSchema: (bool) ($graphql['fastSchema'] ?? true),
            );
        });

        $this->app->singleton(LaravelGraphqlMiddleware::class, function () use ($graphql) {
            $middleware = new LaravelGraphqlMiddleware($this->app->make(Graphql::class));
            return $middleware->withRoutePrefix($graphql['routePrefix'] ?? '/api/graphql');
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/laravel/blackcube-graphql.php' => config_path('blackcube-graphql.php'),
        ], 'blackcube-graphql-config');
    }
}
