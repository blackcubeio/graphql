<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Graphql\Types;

use Blackcube\Dcore\Entities\Parameter;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * GraphQL type of a Parameter (domain, name, value).
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class ParameterType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'name' => 'Parameter',
            'fields' => fn () => [
                'domain' => Type::nonNull(Type::string()),
                'name' => Type::nonNull(Type::string()),
                'value' => Type::string(),
                'dateCreate' => [
                    'type' => Type::string(),
                    'resolve' => fn (Parameter $p) => $p->getDateCreate()?->format('Y-m-d H:i:s'),
                ],
                'dateUpdate' => [
                    'type' => Type::string(),
                    'resolve' => fn (Parameter $p) => $p->getDateUpdate()?->format('Y-m-d H:i:s'),
                ],
            ],
        ]);
    }

    public static function one(mixed $root, array $args): ?Parameter
    {
        return Parameter::query()
            ->andWhere([
                'domain' => $args['domain'],
                'name' => $args['name'],
            ])
            ->one();
    }

    /**
     * @return Parameter[]
     */
    public static function list(): array
    {
        $parametersQuery = Parameter::query()
            ->orderBy([
                'domain' => SORT_ASC,
                'name' => SORT_ASC,
            ]);

        $parameters = [];
        /** @var Parameter $parameter */
        foreach ($parametersQuery->each() as $parameter) {
            $parameters[] = $parameter;
        }
        return $parameters;
    }
}
