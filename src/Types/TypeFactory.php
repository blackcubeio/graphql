<?php

declare(strict_types=1);

/**
 * TypeFactory.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2026 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Graphql\Types;

use Blackcube\Dcore\Enums\ElasticSchemaKind;
use Blackcube\Dcore\Models\ElasticSchema;
use Blackcube\FileProvider\CacheFile;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnionType;
use Swaggest\JsonSchema\Schema;

/**
 * Builds and caches all GraphQL types.
 * Dynamic types are built from ElasticSchema definitions.
 */
final class TypeFactory
{
    /** @var array<string, ObjectType|UnionType> */
    private static array $types = [];

    /** @var array<int, ObjectType> elasticSchemaId → ObjectType */
    private static array $elasticTypes = [];

    private static bool $elasticBuilt = false;

    private static bool $fastSchema = true;

    public static function setFastSchema(bool $fast): void
    {
        self::$fastSchema = $fast;
    }

    public static function content(): ContentType
    {
        return self::$types['Content'] ??= new ContentType();
    }

    public static function tag(): TagType
    {
        return self::$types['Tag'] ??= new TagType();
    }

    public static function slug(): SlugType
    {
        return self::$types['Slug'] ??= new SlugType();
    }

    public static function xeo(): XeoType
    {
        return self::$types['Xeo'] ??= new XeoType();
    }

    public static function bloc(): BlocType
    {
        return self::$types['Bloc'] ??= new BlocType();
    }

    public static function language(): LanguageType
    {
        return self::$types['Language'] ??= new LanguageType();
    }

    public static function parameter(): ParameterType
    {
        return self::$types['Parameter'] ??= new ParameterType();
    }

    public static function author(): AuthorType
    {
        return self::$types['Author'] ??= new AuthorType();
    }

    public static function menu(): MenuType
    {
        return self::$types['Menu'] ??= new MenuType();
    }

    public static function query(): QueryType
    {
        return self::$types['Query'] ??= new QueryType();
    }

    public static function pagination(): PaginationInput
    {
        return self::$types['Pagination'] ??= new PaginationInput();
    }

    public static function contentFilter(): ContentFilterInput
    {
        return self::$types['ContentFilter'] ??= new ContentFilterInput();
    }

    public static function tagFilter(): TagFilterInput
    {
        return self::$types['TagFilter'] ??= new TagFilterInput();
    }

    /**
     * Get the Elastic UnionType (dynamic types from ElasticSchema).
     * Returns null if no schemas exist.
     */
    public static function elastic(): ?UnionType
    {
        self::buildElasticTypes();

        if (empty(self::$elasticTypes)) {
            return null;
        }

        return self::$types['Elastic'] ??= new UnionType([
            'name' => 'Elastic',
            'types' => fn () => array_values(self::$elasticTypes),
            'resolveType' => function (array $value) {
                $schemaId = $value['_elasticSchemaId'] ?? null;
                return self::$elasticTypes[$schemaId] ?? null;
            },
        ]);
    }

    /**
     * Get the ObjectType for a specific ElasticSchema ID.
     */
    public static function elasticType(int $schemaId): ?ObjectType
    {
        self::buildElasticTypes();

        return self::$elasticTypes[$schemaId] ?? null;
    }

    /**
     * Build dynamic ObjectTypes from all active, non-Xeo ElasticSchemas.
     */
    private static function buildElasticTypes(): void
    {
        if (self::$elasticBuilt) {
            return;
        }
        self::$elasticBuilt = true;

        $schemas = ElasticSchema::query()
            ->andWhere(['active' => true])
            ->andWhere(['!=', 'kind', ElasticSchemaKind::Xeo->value])
            ->all();

        foreach ($schemas as $schema) {
            $objectType = self::buildObjectTypeFromSchema($schema);
            if ($objectType !== null) {
                self::$elasticTypes[$schema->getId()] = $objectType;
            }
        }
    }

    private static function buildObjectTypeFromSchema(ElasticSchema $schema): ?ObjectType
    {
        $jsonSchema = $schema->getSchema();
        if ($jsonSchema === null || $jsonSchema === '') {
            return null;
        }

        $decoded = json_decode($jsonSchema);
        if (!$decoded instanceof \stdClass) {
            return null;
        }

        if (self::$fastSchema) {
            $properties = $decoded->properties ?? null;
        } else {
            try {
                $swaggestSchema = Schema::import($decoded);
            } catch (\Throwable) {
                return null;
            }
            $properties = $swaggestSchema->getProperties();
        }

        if ($properties === null) {
            return null;
        }

        $fields = [
            'elasticSchemaId' => [
                'type' => Type::nonNull(Type::int()),
                'resolve' => fn (array $v) => $v['_elasticSchemaId'],
            ],
        ];

        foreach ($properties as $key => $property) {
            $type = $property->type ?? 'string';
            $format = $property->format ?? null;

            if ($type === 'object') {
                $fields[$key] = ['type' => Type::string(), 'resolve' => fn (array $v) => isset($v[$key]) ? json_encode($v[$key]) : null];
                continue;
            }

            if ($type === 'string' && $format === 'file') {
                $fields[$key] = [
                    'type' => Type::string(),
                    'resolve' => static function (array $v, array $args, array $context) use ($key): ?string {
                        $path = $v[$key] ?? null;
                        if ($path === null || $path === '') {
                            return null;
                        }
                        $url = (string) CacheFile::from($path);
                        if ($url === '') {
                            return null;
                        }
                        if (str_starts_with($url, '/')) {
                            return 'https://' . ($context['host'] ?? '') . $url;
                        }
                        return $url;
                    },
                ];
                continue;
            }

            if ($type === 'string' && $format === 'files') {
                $fields[$key] = [
                    'type' => Type::listOf(Type::string()),
                    'resolve' => static function (array $v, array $args, array $context) use ($key): array {
                        $raw = $v[$key] ?? '';
                        if ($raw === '') {
                            return [];
                        }
                        $host = $context['host'] ?? '';
                        $result = [];
                        foreach (preg_split('/\s*,\s*/', $raw, -1, PREG_SPLIT_NO_EMPTY) as $path) {
                            $url = (string) CacheFile::from($path);
                            if ($url === '') {
                                continue;
                            }
                            $result[] = str_starts_with($url, '/') ? 'https://' . $host . $url : $url;
                        }
                        return $result;
                    },
                ];
                continue;
            }

            $fields[$key] = match ($type) {
                'boolean' => Type::boolean(),
                'number' => Type::float(),
                'integer' => Type::int(),
                default => Type::string(),
            };
        }

        $transliterator = \Transliterator::createFromRules(
            ':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: NFC;',
            \Transliterator::FORWARD
        );
        $normalized = $transliterator->transliterate($schema->getName());
        $name = 'Elastic' . str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $normalized)));

        return new ObjectType([
            'name' => $name,
            'fields' => fn () => $fields,
        ]);
    }

    /**
     * Reset cached types (for tests).
     */
    public static function reset(): void
    {
        self::$types = [];
        self::$elasticTypes = [];
        self::$elasticBuilt = false;
    }
}
