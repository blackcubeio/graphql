<?php

declare(strict_types=1);

/**
 * PsrGraphqlMiddlewareCest.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Graphql\Tests\Integration;

use Blackcube\Dcore\Models\Language;
use Blackcube\Dcore\Services\JsonLdBuilder;
use Blackcube\Graphql\Handlers\Graphql;
use Blackcube\Graphql\Middlewares\PreviewMiddleware;
use Blackcube\Graphql\Middlewares\PsrGraphqlMiddleware;
use Blackcube\Graphql\Tests\Support\DatabaseCestTrait;
use Blackcube\Graphql\Tests\Support\IntegrationTester;
use Blackcube\Graphql\Tests\Support\Stub\PassthroughHandler;
use HttpSoft\Message\ResponseFactory;
use HttpSoft\Message\ServerRequestFactory;
use HttpSoft\Message\StreamFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class PsrGraphqlMiddlewareCest
{
    use DatabaseCestTrait;

    private function createLanguage(): Language
    {
        $language = Language::query()->andWhere(['id' => 'fr'])->one();
        if ($language === null) {
            $language = new Language();
            $language->setId('fr');
            $language->setName('Français');
            $language->setActive(true);
            $language->setMain(true);
            $language->save();
        }
        return $language;
    }

    private function createMiddleware(): PsrGraphqlMiddleware
    {
        $graphqlHandler = new Graphql(
            new ResponseFactory(),
            new StreamFactory(),
            new JsonLdBuilder(),
            debug: true,
            fastSchema: false,
        );
        return new PsrGraphqlMiddleware($graphqlHandler, new PreviewMiddleware());
    }

    private function createGraphqlRequest(string $method, string $url): ServerRequestInterface
    {
        $stream = (new StreamFactory())->createStream('{"query": "{ languages { id } }"}');
        return (new ServerRequestFactory())
            ->createServerRequest($method, $url)
            ->withBody($stream);
    }

    private function dispatchMiddleware(PsrGraphqlMiddleware $middleware, ServerRequestInterface $request): ResponseInterface
    {
        return $middleware->process($request, new PassthroughHandler(new ResponseFactory()));
    }

    public function testPostOnRoutePrefixAnswersGraphql(IntegrationTester $I): void
    {
        $I->wantTo('answer a POST on the route prefix through the real Graphql handler');
        $this->createLanguage();

        $middleware = $this->createMiddleware();
        $graphqlRequest = $this->createGraphqlRequest('POST', 'https://test.local/api/graphql');
        $response = $this->dispatchMiddleware($middleware, $graphqlRequest);

        $I->assertSame(200, $response->getStatusCode());
        $decodedBody = json_decode((string) $response->getBody(), true);
        $I->assertArrayNotHasKey('errors', $decodedBody, 'GraphQL errors: '.json_encode($decodedBody['errors'] ?? []));
        $languageIds = array_column($decodedBody['data']['languages'], 'id');
        $I->assertContains('fr', $languageIds);
    }

    public function testGetOnRoutePrefixIsHandedOver(IntegrationTester $I): void
    {
        $I->wantTo('hand a GET on the route prefix over to the next handler');

        $middleware = $this->createMiddleware();
        $graphqlRequest = $this->createGraphqlRequest('GET', 'https://test.local/api/graphql');
        $response = $this->dispatchMiddleware($middleware, $graphqlRequest);

        $I->assertSame(PassthroughHandler::STATUS_CODE, $response->getStatusCode());
    }

    public function testPostOnAnotherPathIsHandedOver(IntegrationTester $I): void
    {
        $I->wantTo('hand a POST on another path over to the next handler');

        $middleware = $this->createMiddleware();
        $graphqlRequest = $this->createGraphqlRequest('POST', 'https://test.local/some-page');
        $response = $this->dispatchMiddleware($middleware, $graphqlRequest);

        $I->assertSame(PassthroughHandler::STATUS_CODE, $response->getStatusCode());
    }

    public function testWithRoutePrefixMovesTheEndpoint(IntegrationTester $I): void
    {
        $I->wantTo('answer on a custom route prefix and hand the default one over');
        $this->createLanguage();

        $middleware = $this->createMiddleware()->withRoutePrefix('/gql');

        $customPrefixRequest = $this->createGraphqlRequest('POST', 'https://test.local/gql');
        $response = $this->dispatchMiddleware($middleware, $customPrefixRequest);
        $I->assertSame(200, $response->getStatusCode());

        $defaultPrefixRequest = $this->createGraphqlRequest('POST', 'https://test.local/api/graphql');
        $response = $this->dispatchMiddleware($middleware, $defaultPrefixRequest);
        $I->assertSame(PassthroughHandler::STATUS_CODE, $response->getStatusCode());
    }
}
