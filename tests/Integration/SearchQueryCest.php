<?php

declare(strict_types=1);

/**
 * SearchQueryCest.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Graphql\Tests\Integration;

use Blackcube\Dcore\Models\Content;
use Blackcube\Dcore\Models\Language;
use Blackcube\Graphql\Tests\Support\DatabaseCestTrait;
use Blackcube\Graphql\Tests\Support\GraphqlExecutorTrait;
use Blackcube\Graphql\Tests\Support\IntegrationTester;

final class SearchQueryCest
{
    use DatabaseCestTrait;
    use GraphqlExecutorTrait;

    private function createLanguage(): void
    {
        $language = Language::query()->andWhere(['id' => 'fr'])->one();
        if ($language === null) {
            $language = new Language();
            $language->setId('fr');
            $language->setName('Français');
            $language->setMain(true);
        }
        $language->setActive(true);
        $language->save();
    }

    private function createContent(string $name, bool $active): Content
    {
        $content = new Content();
        $content->setName($name);
        $content->setLanguageId('fr');
        $content->setActive($active);
        $content->save();
        return $content;
    }

    public function searchReturnsMatchingPublishedContent(IntegrationTester $I): void
    {
        $this->createLanguage();
        $this->createContent('Zorglub published article', true);

        $result = $this->executeGraphql('{ search(query: "Zorglub") { contents { name } tags { name } } }');

        $I->assertArrayNotHasKey('errors', $result, 'GraphQL errors: '.json_encode($result['errors'] ?? []));
        $names = array_column($result['data']['search']['contents'], 'name');
        $I->assertContains('Zorglub published article', $names);
    }

    public function searchExcludesUnpublishedContent(IntegrationTester $I): void
    {
        $this->createLanguage();
        $this->createContent('Wakanda visible page', true);
        $this->createContent('Wakanda hidden page', false);

        $result = $this->executeGraphql('{ search(query: "Wakanda") { contents { name active } } }');

        $I->assertArrayNotHasKey('errors', $result, 'GraphQL errors: '.json_encode($result['errors'] ?? []));
        $names = array_column($result['data']['search']['contents'], 'name');
        $I->assertContains('Wakanda visible page', $names);
        $I->assertNotContains('Wakanda hidden page', $names);
    }

    public function searchEmptyQueryReturnsEmptyResult(IntegrationTester $I): void
    {
        $this->createLanguage();

        $result = $this->executeGraphql('{ search(query: "") { contents { id } tags { id } } }');

        $I->assertArrayNotHasKey('errors', $result, 'GraphQL errors: '.json_encode($result['errors'] ?? []));
        $I->assertSame([], $result['data']['search']['contents']);
        $I->assertSame([], $result['data']['search']['tags']);
    }
}
