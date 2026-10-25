<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Graphql\Types;

use Blackcube\Dcore\Interfaces\JsonLdBuilderInterface;
use Blackcube\Dcore\Models\Slug;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * GraphQL type of a Slug: path, host, Xeo metadata and JSON-LD.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class SlugType extends ObjectType
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
                    'resolve' => fn (Slug $slug) => $slug->getHostQuery()->one()?->getName(),
                ],
                'active' => Type::nonNull(Type::boolean()),
                'dateCreate' => [
                    'type' => Type::string(),
                    'resolve' => fn (Slug $slug) => $slug->getDateCreate()?->format('Y-m-d H:i:s'),
                ],
                'dateUpdate' => [
                    'type' => Type::string(),
                    'resolve' => fn (Slug $slug) => $slug->getDateUpdate()?->format('Y-m-d H:i:s'),
                ],
                'xeo' => [
                    'type' => TypeFactory::xeo(),
                    'resolve' => fn (Slug $slug) => $slug->getXeoQuery()->one(),
                ],
                'jsonLd' => [
                    'type' => Type::string(),
                    'resolve' => fn (Slug $slug, array $args, array $context) => self::buildJsonLd($slug, $context),
                ],
            ],
        ]);
    }

    private static function buildJsonLd(Slug $slug, array $context): ?string
    {
        $jsonLd = null;
        $builder = $context['jsonLdBuilder'] ?? null;
        $host = $context['host'] ?? '';
        if (($builder instanceof JsonLdBuilderInterface) === true) {
            $jsonLdData = $builder->build($slug->getId(), $host);
            if (empty($jsonLdData) === false) {
                $jsonLd = json_encode($jsonLdData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
        }
        return $jsonLd;
    }
}
