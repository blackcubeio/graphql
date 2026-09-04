<?php

declare(strict_types=1);

/**
 * LaravelGraphqlMiddleware.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Graphql\Middlewares;

use Blackcube\Graphql\Handlers\Graphql;
use Closure;
use HttpSoft\Message\ServerRequestFactory;
use HttpSoft\Message\StreamFactory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Laravel implementation of the GraphQL endpoint middleware.
 *
 * Converts the incoming request to PSR-7, delegates to the Graphql handler,
 * converts the response back to a Laravel response. Every other request is
 * handed over to the next middleware.
 */
class LaravelGraphqlMiddleware
{
    private string $routePrefix = '/api/graphql';

    public function __construct(
        private readonly Graphql $graphqlHandler,
    ) {
    }

    public function withRoutePrefix(string $routePrefix): static
    {
        $new = clone $this;
        $new->routePrefix = '/'.ltrim($routePrefix, '/');
        return $new;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $path = '/'.ltrim($request->getPathInfo(), '/');
        if ($request->isMethod('POST') === true && $path === $this->routePrefix) {
            $streamFactory = new StreamFactory();
            $psrRequest = (new ServerRequestFactory())
                ->createServerRequest($request->method(), $request->fullUrl(), $_SERVER)
                ->withQueryParams($request->query->all())
                ->withBody($streamFactory->createStream($request->getContent()));
            foreach ($request->headers->all() as $headerName => $headerValues) {
                $psrRequest = $psrRequest->withHeader($headerName, $headerValues);
            }

            $psrResponse = $this->graphqlHandler->handle($psrRequest);

            $response = new Response($psrResponse->getBody()->getContents(), $psrResponse->getStatusCode());
            foreach ($psrResponse->getHeaders() as $headerName => $headerValues) {
                foreach ($headerValues as $headerValue) {
                    $response->headers->set($headerName, $headerValue, false);
                }
            }
        } else {
            $response = $next($request);
        }
        return $response;
    }
}
