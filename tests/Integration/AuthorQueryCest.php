<?php

declare(strict_types=1);

/**
 * AuthorQueryCest.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Graphql\Tests\Integration;

use Blackcube\Dcore\Models\Author;
use Blackcube\Graphql\Tests\Support\DatabaseCestTrait;
use Blackcube\Graphql\Tests\Support\GraphqlExecutorTrait;
use Blackcube\Graphql\Tests\Support\IntegrationTester;

final class AuthorQueryCest
{
    use DatabaseCestTrait;
    use GraphqlExecutorTrait;

    private function createAuthor(string $firstname, string $lastname): Author
    {
        $author = new Author();
        $author->setFirstname($firstname);
        $author->setLastname($lastname);
        $author->setActive(true);
        $author->save();
        return $author;
    }

    public function authorByIdReturnsScalarFields(IntegrationTester $I): void
    {
        $author = $this->createAuthor('Ada', 'Lovelace');
        $authorId = (int) $author->getId();

        $result = $this->executeGraphql(
            'query ($id: Int!) { author(id: $id) { id firstname lastname active } }',
            ['id' => $authorId]
        );

        $I->assertArrayNotHasKey('errors', $result, 'GraphQL errors: '.json_encode($result['errors'] ?? []));
        $I->assertSame($authorId, $result['data']['author']['id']);
        $I->assertSame('Ada', $result['data']['author']['firstname']);
        $I->assertSame('Lovelace', $result['data']['author']['lastname']);
        $I->assertTrue($result['data']['author']['active']);
    }

    public function authorsListReturnsActiveAuthorsSorted(IntegrationTester $I): void
    {
        $this->createAuthor('Grace', 'Hopper');
        $this->createAuthor('Ada', 'Lovelace');

        $result = $this->executeGraphql('{ authors { firstname lastname } }');

        $I->assertArrayNotHasKey('errors', $result, 'GraphQL errors: '.json_encode($result['errors'] ?? []));
        $lastnames = array_column($result['data']['authors'], 'lastname');
        $I->assertContains('Hopper', $lastnames);
        $I->assertContains('Lovelace', $lastnames);
        $hopperIndex = array_search('Hopper', $lastnames, true);
        $lovelaceIndex = array_search('Lovelace', $lastnames, true);
        $I->assertLessThan($lovelaceIndex, $hopperIndex, 'Authors should be ordered by lastname ascending');
    }
}
