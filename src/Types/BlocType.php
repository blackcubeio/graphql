<?php

declare(strict_types=1);

/**
 * BlocType.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2026 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Graphql\Types;

use Blackcube\Dcore\Entities\Bloc;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

final class BlocType extends ObjectType
{
    /**
     * @return array<string, mixed>
     */
    public static function elasticField(): array
    {
        $type = TypeFactory::elastic();
        if ($type === null) {
            return [];
        }

        return [
            'elastic' => [
                'type' => $type,
                'resolve' => function (Bloc $b): ?array {
                    $schemaId = $b->getElasticSchemaId();
                    if ($schemaId === null || TypeFactory::elasticType((int) $schemaId) === null) {
                        return null;
                    }

                    return ['_elasticSchemaId' => (int) $schemaId] + $b->getElasticValues();
                },
            ],
        ];
    }

    public function __construct()
    {
        parent::__construct([
            'name' => 'Bloc',
            'fields' => fn () => [
                'id' => Type::nonNull(Type::int()),
                'elasticSchemaId' => Type::int(),
                'active' => Type::nonNull(Type::boolean()),
                'dateCreate' => [
                    'type' => Type::string(),
                    'resolve' => fn (Bloc $b) => $b->getDateCreate()?->format('Y-m-d H:i:s'),
                ],
                'dateUpdate' => [
                    'type' => Type::string(),
                    'resolve' => fn (Bloc $b) => $b->getDateUpdate()?->format('Y-m-d H:i:s'),
                ],
            ] + self::elasticField(),
        ]);
    }
}
