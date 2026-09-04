<?php

declare(strict_types=1);

/**
 * PsrGraphqlMiddleware.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Graphql\Middlewares;

use Blackcube\Graphql\Handlers\Graphql;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * PSR-15 implementation of the GraphQL endpoint middleware.
 *
 * Answers POST requests on the route prefix through the Graphql handler,
 * hands every other request over to the next handler.
 */
class PsrGraphqlMiddleware implements MiddlewareInterface
{
    private string $routePrefix = '/api/graphql';

    public function __construct(
        private readonly Graphql $graphqlHandler,
        private readonly PreviewMiddleware $previewMiddleware,
    ) {
    }

    public function withRoutePrefix(string $routePrefix): static
    {
        $new = clone $this;
        $new->routePrefix = '/'.ltrim($routePrefix, '/');
        return $new;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $path = '/'.ltrim($request->getUri()->getPath(), '/');
        if ($request->getMethod() === 'POST' && $path === $this->routePrefix) {
            $response = $this->previewMiddleware->process($request, $this->graphqlHandler);
        } else {
            $response = $handler->handle($request);
        }
        return $response;
    }
}
