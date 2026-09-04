<?php

declare(strict_types=1);

/**
 * ContentQueryCest.php
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

final class ContentQueryCest
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

    private function createContent(string $name, ?Content $parent = null): Content
    {
        $content = new Content();
        $content->setName($name);
        $content->setLanguageId('fr');
        $content->setActive(true);
        if ($parent !== null) {
            $content->saveInto($parent);
        } else {
            $content->save();
        }
        return $content;
    }

    public function contentByIdReturnsScalarFields(IntegrationTester $I): void
    {
        $this->createLanguage();
        $content = $this->createContent('GraphQL Test Content');
        $contentId = (int) $content->getId();

        $result = $this->executeGraphql(
            'query ($id: Int!) { content(id: $id) { id name languageId active } }',
            ['id' => $contentId]
        );

        $I->assertArrayNotHasKey('errors', $result, 'GraphQL errors: '.json_encode($result['errors'] ?? []));
        $I->assertSame($contentId, $result['data']['content']['id']);
        $I->assertSame('GraphQL Test Content', $result['data']['content']['name']);
        $I->assertSame('fr', $result['data']['content']['languageId']);
        $I->assertTrue($result['data']['content']['active']);
    }

    public function contentByUnknownIdReturnsNull(IntegrationTester $I): void
    {
        $this->createLanguage();

        $result = $this->executeGraphql('{ content(id: 999999) { id } }');

        $I->assertArrayNotHasKey('errors', $result, 'GraphQL errors: '.json_encode($result['errors'] ?? []));
        $I->assertNull($result['data']['content']);
    }

    public function contentsListReturnsCreatedContents(IntegrationTester $I): void
    {
        $this->createLanguage();
        $this->createContent('Alpha');
        $this->createContent('Beta');

        $result = $this->executeGraphql('{ contents { id name } }');

        $I->assertArrayNotHasKey('errors', $result, 'GraphQL errors: '.json_encode($result['errors'] ?? []));
        $names = array_column($result['data']['contents'], 'name');
        $I->assertContains('Alpha', $names);
        $I->assertContains('Beta', $names);
    }

    public function contentsFilterByLanguageId(IntegrationTester $I): void
    {
        $this->createLanguage();
        $this->createContent('FrenchContent');

        $result = $this->executeGraphql('{ contents(filters: { languageId: "fr" }) { languageId } }');

        $I->assertArrayNotHasKey('errors', $result, 'GraphQL errors: '.json_encode($result['errors'] ?? []));
        $I->assertNotEmpty($result['data']['contents']);
        foreach ($result['data']['contents'] as $content) {
            $I->assertSame('fr', $content['languageId']);
        }
    }

    public function contentsPaginationLimitsResults(IntegrationTester $I): void
    {
        $this->createLanguage();
        $this->createContent('Page1');
        $this->createContent('Page2');
        $this->createContent('Page3');

        $result = $this->executeGraphql('{ contents(pagination: { size: 2 }) { id } }');

        $I->assertArrayNotHasKey('errors', $result, 'GraphQL errors: '.json_encode($result['errors'] ?? []));
        $I->assertCount(2, $result['data']['contents']);
    }

    public function contentChildrenAndParentResolve(IntegrationTester $I): void
    {
        $this->createLanguage();
        $parent = $this->createContent('ParentNode');
        $this->createContent('ChildNode', $parent);
        $parentId = (int) $parent->getId();

        $result = $this->executeGraphql(
            'query ($id: Int!) { content(id: $id) { name children { name parent { name } } } }',
            ['id' => $parentId]
        );

        $I->assertArrayNotHasKey('errors', $result, 'GraphQL errors: '.json_encode($result['errors'] ?? []));
        $children = $result['data']['content']['children'];
        $childNames = array_column($children, 'name');
        $I->assertContains('ChildNode', $childNames);
        $I->assertSame('ParentNode', $children[0]['parent']['name']);
    }
}
