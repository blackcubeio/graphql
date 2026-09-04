<?php

declare(strict_types=1);

/**
 * ParameterQueryCest.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Graphql\Tests\Integration;

use Blackcube\Dcore\Models\Parameter;
use Blackcube\Graphql\Tests\Support\DatabaseCestTrait;
use Blackcube\Graphql\Tests\Support\GraphqlExecutorTrait;
use Blackcube\Graphql\Tests\Support\IntegrationTester;

final class ParameterQueryCest
{
    use DatabaseCestTrait;
    use GraphqlExecutorTrait;

    private function createParameter(string $domain, string $name, string $value): Parameter
    {
        $parameter = new Parameter();
        $parameter->setDomain($domain);
        $parameter->setName($name);
        $parameter->setValue($value);
        $parameter->save();
        return $parameter;
    }

    public function parameterByDomainAndNameReturnsValue(IntegrationTester $I): void
    {
        $this->createParameter('seo', 'siteName', 'Blackcube');

        $result = $this->executeGraphql('{ parameter(domain: "seo", name: "siteName") { domain name value } }');

        $I->assertArrayNotHasKey('errors', $result, 'GraphQL errors: '.json_encode($result['errors'] ?? []));
        $I->assertSame('seo', $result['data']['parameter']['domain']);
        $I->assertSame('siteName', $result['data']['parameter']['name']);
        $I->assertSame('Blackcube', $result['data']['parameter']['value']);
    }

    public function parametersListReturnsCreatedParameters(IntegrationTester $I): void
    {
        $this->createParameter('analytics', 'trackingId', 'UA-123');
        $this->createParameter('mail', 'sender', 'noreply@blackcube.io');

        $result = $this->executeGraphql('{ parameters { domain name } }');

        $I->assertArrayNotHasKey('errors', $result, 'GraphQL errors: '.json_encode($result['errors'] ?? []));
        $names = array_column($result['data']['parameters'], 'name');
        $I->assertContains('trackingId', $names);
        $I->assertContains('sender', $names);
    }
}
