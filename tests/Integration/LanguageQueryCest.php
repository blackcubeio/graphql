<?php

declare(strict_types=1);

/**
 * LanguageQueryCest.php
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

final class LanguageQueryCest
{
    use DatabaseCestTrait;
    use GraphqlExecutorTrait;

    private function createLanguage(string $id, string $name, bool $main): Language
    {
        $language = Language::query()->andWhere(['id' => $id])->one();
        if ($language === null) {
            $language = new Language();
            $language->setId($id);
            $language->setName($name);
            $language->setMain($main);
        }
        $language->setActive(true);
        $language->save();
        return $language;
    }

    public function languageByIdReturnsScalarFields(IntegrationTester $I): void
    {
        $this->createLanguage('fr', 'Français', true);

        $result = $this->executeGraphql('{ language(id: "fr") { id name main active } }');

        $I->assertArrayNotHasKey('errors', $result, 'GraphQL errors: '.json_encode($result['errors'] ?? []));
        $I->assertSame('fr', $result['data']['language']['id']);
        $I->assertSame('Français', $result['data']['language']['name']);
        $I->assertTrue($result['data']['language']['main']);
        $I->assertTrue($result['data']['language']['active']);
    }

    public function languagesListReturnsActiveLanguages(IntegrationTester $I): void
    {
        $this->createLanguage('fr', 'Français', true);
        $this->createLanguage('en', 'English', false);

        $result = $this->executeGraphql('{ languages { id name } }');

        $I->assertArrayNotHasKey('errors', $result, 'GraphQL errors: '.json_encode($result['errors'] ?? []));
        $ids = array_column($result['data']['languages'], 'id');
        $I->assertContains('fr', $ids);
        $I->assertContains('en', $ids);
    }
}
