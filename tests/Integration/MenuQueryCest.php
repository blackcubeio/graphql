<?php

declare(strict_types=1);

/**
 * MenuQueryCest.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Graphql\Tests\Integration;

use Blackcube\Dcore\Models\Language;
use Blackcube\Dcore\Models\Menu;
use Blackcube\Graphql\Tests\Support\DatabaseCestTrait;
use Blackcube\Graphql\Tests\Support\GraphqlExecutorTrait;
use Blackcube\Graphql\Tests\Support\IntegrationTester;

final class MenuQueryCest
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

    private function createMenu(string $name): Menu
    {
        $menu = new Menu();
        $menu->setName($name);
        $menu->setLanguageId('fr');
        $menu->setActive(true);
        $menu->save();
        return $menu;
    }

    public function menuByIdReturnsScalarFields(IntegrationTester $I): void
    {
        $this->createLanguage();
        $menu = $this->createMenu('Main Menu');
        $menuId = (int) $menu->getId();

        $result = $this->executeGraphql(
            'query ($id: Int!) { menu(id: $id) { id name languageId active } }',
            ['id' => $menuId]
        );

        $I->assertArrayNotHasKey('errors', $result, 'GraphQL errors: '.json_encode($result['errors'] ?? []));
        $I->assertSame($menuId, $result['data']['menu']['id']);
        $I->assertSame('Main Menu', $result['data']['menu']['name']);
        $I->assertSame('fr', $result['data']['menu']['languageId']);
        $I->assertTrue($result['data']['menu']['active']);
    }

    public function menusListReturnsCreatedMenus(IntegrationTester $I): void
    {
        $this->createLanguage();
        $this->createMenu('Header');
        $this->createMenu('Footer');

        $result = $this->executeGraphql('{ menus { name } }');

        $I->assertArrayNotHasKey('errors', $result, 'GraphQL errors: '.json_encode($result['errors'] ?? []));
        $names = array_column($result['data']['menus'], 'name');
        $I->assertContains('Header', $names);
        $I->assertContains('Footer', $names);
    }
}
