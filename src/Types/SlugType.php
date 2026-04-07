<?php

declare(strict_types=1);

/**
 * SlugType.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2026 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Graphql\Types;

use Blackcube\Dcore\Interfaces\JsonLdBuilderInterface;
use Blackcube\Dcore\Models\Slug;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

final class SlugType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'name' => 'Slug',
            'fields' => fn () => [
                'id' => Type::nonNull(Type::int()),
                'path' => Type::nonNull(Type::string()),
                'host' => [
                    'type' => Type::string(),
                    'resolve' => fn (Slug $s) => $s->getHostQuery()->one()?->getName(),
                ],
                'active' => Type::nonNull(Type::boolean()),
                'dateCreate' => [
                    'type' => Type::string(),
                    'resolve' => fn (Slug $s) => $s->getDateCreate()?->format('Y-m-d H:i:s'),
                ],
                'dateUpdate' => [
                    'type' => Type::string(),
                    'resolve' => fn (Slug $s) => $s->getDateUpdate()?->format('Y-m-d H:i:s'),
                ],
                'xeo' => [
                    'type' => TypeFactory::xeo(),
                    'resolve' => fn (Slug $s) => $s->getXeoQuery()->one(),
                ],
                'jsonLd' => [
                    'type' => Type::string(),
                    'resolve' => function (Slug $s, array $args, array $context): ?string {
                        $builder = $context['jsonLdBuilder'] ?? null;
                        $host = $context['host'] ?? '';
                        if (!$builder instanceof JsonLdBuilderInterface) {
                            return null;
                        }
                        $result = $builder->build($s->getId(), $host);

                        return empty($result) ? null : json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                    },
                ],
            ],
        ]);
    }
}
