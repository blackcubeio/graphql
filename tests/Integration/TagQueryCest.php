<?php

declare(strict_types=1);

/**
 * TagQueryCest.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Graphql\Tests\Integration;

use Blackcube\Dcore\Models\Tag;
use Blackcube\Graphql\Tests\Support\DatabaseCestTrait;
use Blackcube\Graphql\Tests\Support\GraphqlExecutorTrait;
use Blackcube\Graphql\Tests\Support\IntegrationTester;

final class TagQueryCest
{
    use DatabaseCestTrait;
    use GraphqlExecutorTrait;

    private function createTag(string $name): Tag
    {
        $tag = new Tag();
        $tag->setName($name);
        $tag->setActive(true);
        $tag->save();
        return $tag;
    }

    public function tagByIdReturnsScalarFields(IntegrationTester $I): void
    {
        $tag = $this->createTag('News');
        $tagId = (int) $tag->getId();

        $result = $this->executeGraphql(
            'query ($id: Int!) { tag(id: $id) { id name active } }',
            ['id' => $tagId]
        );

        $I->assertArrayNotHasKey('errors', $result, 'GraphQL errors: '.json_encode($result['errors'] ?? []));
        $I->assertSame($tagId, $result['data']['tag']['id']);
        $I->assertSame('News', $result['data']['tag']['name']);
        $I->assertTrue($result['data']['tag']['active']);
    }

    public function tagsListReturnsCreatedTagsSortedByName(IntegrationTester $I): void
    {
        $this->createTag('Zeta');
        $this->createTag('Alpha');

        $result = $this->executeGraphql('{ tags { name } }');

        $I->assertArrayNotHasKey('errors', $result, 'GraphQL errors: '.json_encode($result['errors'] ?? []));
        $names = array_column($result['data']['tags'], 'name');
        $I->assertContains('Alpha', $names);
        $I->assertContains('Zeta', $names);
        $alphaIndex = array_search('Alpha', $names, true);
        $zetaIndex = array_search('Zeta', $names, true);
        $I->assertLessThan($zetaIndex, $alphaIndex, 'Tags should be ordered by name ascending');
    }
}
