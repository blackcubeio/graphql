<?php

declare(strict_types=1);

/**
 * GraphqlExecutorTrait.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Graphql\Tests\Support;

use Blackcube\Dcore\Services\JsonLdBuilder;
use Blackcube\Graphql\Handlers\Graphql;
use HttpSoft\Message\ResponseFactory;
use HttpSoft\Message\ServerRequestFactory;
use HttpSoft\Message\StreamFactory;

/**
 * Executes GraphQL queries through the real PSR-15 handler.
 */
trait GraphqlExecutorTrait
{
    /**
     * Execute a GraphQL query with a parsed body and return the decoded JSON.
     *
     * @param array<string, mixed>|null $variables
     * @return array<string, mixed>
     */
    private function executeGraphql(string $query, ?array $variables = null): array
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest('POST', 'https://test.local/graphql')
            ->withParsedBody(['query' => $query, 'variables' => $variables]);

        return $this->dispatchGraphql($request);
    }

    /**
     * Execute a GraphQL query sent as a raw JSON body (no parsed body) and return the decoded JSON.
     *
     * @return array<string, mixed>
     */
    private function executeGraphqlRawBody(string $json): array
    {
        $stream = (new StreamFactory())->createStream($json);
        $request = (new ServerRequestFactory())
            ->createServerRequest('POST', 'https://test.local/graphql')
            ->withBody($stream);

        return $this->dispatchGraphql($request);
    }

    /**
     * @return array<string, mixed>
     */
    private function dispatchGraphql(\Psr\Http\Message\ServerRequestInterface $request): array
    {
        $handler = new Graphql(
            new ResponseFactory(),
            new StreamFactory(),
            new JsonLdBuilder(),
            debug: true,
            fastSchema: false,
        );

        $response = $handler->handle($request);

        return json_decode((string) $response->getBody(), true);
    }
}
