<?php

declare(strict_types=1);

/**
 * GraphqlHandlerCest.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Graphql\Tests\Integration;

use Blackcube\Dcore\Models\Language;
use Blackcube\Graphql\Tests\Support\DatabaseCestTrait;
use Blackcube\Graphql\Tests\Support\GraphqlExecutorTrait;
use Blackcube\Graphql\Tests\Support\IntegrationTester;

final class GraphqlHandlerCest
{
    use DatabaseCestTrait;
    use GraphqlExecutorTrait;

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

    public function handlerExecutesQueryFromRawJsonBody(IntegrationTester $I): void
    {
        $this->createLanguage();

        $result = $this->executeGraphqlRawBody('{"query": "{ languages { id } }"}');

        $I->assertArrayNotHasKey('errors', $result, 'GraphQL errors: '.json_encode($result['errors'] ?? []));
        $ids = array_column($result['data']['languages'], 'id');
        $I->assertContains('fr', $ids);
    }

    public function handlerReturnsErrorsForUnknownField(IntegrationTester $I): void
    {
        $this->createLanguage();

        $result = $this->executeGraphql('{ thisFieldDoesNotExist }');

        $I->assertArrayHasKey('errors', $result);
        $I->assertNotEmpty($result['errors']);
    }

    public function handlerSupportsQueryVariables(IntegrationTester $I): void
    {
        $this->createLanguage();

        $result = $this->executeGraphql(
            'query ($id: String!) { language(id: $id) { id } }',
            ['id' => 'fr']
        );

        $I->assertArrayNotHasKey('errors', $result, 'GraphQL errors: '.json_encode($result['errors'] ?? []));
        $I->assertSame('fr', $result['data']['language']['id']);
    }
}
