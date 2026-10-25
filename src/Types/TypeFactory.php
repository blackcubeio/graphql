<?php

declare(strict_types=1);

/**
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Graphql\Types;

use Blackcube\Dcore\Enums\ElasticSchemaKind;
use Blackcube\Dcore\Models\ElasticSchema;
use Blackcube\FileProvider\CacheFile;
use Blackcube\Injector\Injector;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnionType;
use Psr\Log\LoggerInterface;
use Swaggest\JsonSchema\Schema;

/**
 * Builds and caches all GraphQL types.
 * Dynamic types are built from ElasticSchema definitions.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class TypeFactory
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

    public static function searchResult(): SearchResultType
    {
        return self::$types['SearchResult'] ??= new SearchResultType();
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
        $elasticType = null;
        self::buildElasticTypes();

        if (empty(self::$elasticTypes) === false) {
            if (isset(self::$types['Elastic']) === false) {
                self::$types['Elastic'] = new UnionType([
                    'name' => 'Elastic',
                    'types' => fn () => array_values(self::$elasticTypes),
                    'resolveType' => fn (array $value) => self::findElasticType($value),
                ]);
            }
            $elasticType = self::$types['Elastic'];
        }

        return $elasticType;
    }

    /**
     * ObjectType of an elastic value, from its _elasticSchemaId.
     */
    private static function findElasticType(array $value): ?ObjectType
    {
        $schemaId = $value['_elasticSchemaId'] ?? null;
        return self::$elasticTypes[$schemaId] ?? null;
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
        if (self::$elasticBuilt === false) {
            self::$elasticBuilt = true;

            $schemasQuery = ElasticSchema::query()
                ->andWhere(['active' => true])
                ->andWhere([
                    '!=',
                    'kind',
                    ElasticSchemaKind::Xeo->value,
                ]);

            /** @var ElasticSchema $schema */
            foreach ($schemasQuery->each() as $schema) {
                $objectType = self::buildObjectTypeFromSchema($schema);
                if ($objectType !== null) {
                    self::$elasticTypes[$schema->getId()] = $objectType;
                }
            }
        }
    }

    private static function buildObjectTypeFromSchema(ElasticSchema $schema): ?ObjectType
    {
        $objectType = null;
        $properties = null;
        $jsonSchema = $schema->getSchema();
        if ($jsonSchema !== null && $jsonSchema !== '') {
            $decoded = json_decode($jsonSchema);
            if (($decoded instanceof \stdClass) === true) {
                if (self::$fastSchema === true) {
                    $properties = $decoded->properties ?? null;
                } else {
                    $swaggestSchema = null;
                    try {
                        $swaggestSchema = Schema::import($decoded);
                    } catch (\Throwable $e) {
                        $swaggestSchema = null;
                        if (Injector::has(LoggerInterface::class) === true) {
                            Injector::get(LoggerInterface::class)->error('Elastic schema could not be imported for GraphQL', [
                                'elasticSchemaId' => $schema->getId(),
                                'exception' => $e,
                            ]);
                        }
                    }
                    if ($swaggestSchema !== null) {
                        $properties = $swaggestSchema->getProperties();
                    }
                }
            }
        }

        if ($properties !== null) {
            $fields = [
                'elasticSchemaId' => [
                    'type' => Type::nonNull(Type::int()),
                    'resolve' => fn (array $values) => $values['_elasticSchemaId'],
                ],
            ];

            foreach ($properties as $key => $property) {
                $type = $property->type ?? 'string';
                $format = $property->format ?? null;

                if ($type === 'object') {
                    $fields[$key] = [
                        'type' => Type::string(),
                        'resolve' => fn (array $values) => isset($values[$key]) === true ? json_encode($values[$key]) : null,
                    ];
                } elseif ($type === 'string' && $format === 'file') {
                    $fields[$key] = [
                        'type' => Type::string(),
                        'resolve' => fn (array $values, array $args, array $context) => self::buildFileUrl($values[$key] ?? null, $context),
                    ];
                } elseif ($type === 'string' && $format === 'files') {
                    $fields[$key] = [
                        'type' => Type::listOf(Type::string()),
                        'resolve' => fn (array $values, array $args, array $context) => self::buildFileUrls($values[$key] ?? '', $context),
                    ];
                } else {
                    $fields[$key] = match ($type) {
                        'boolean' => Type::boolean(),
                        'number' => Type::float(),
                        'integer' => Type::int(),
                        default => Type::string(),
                    };
                }
            }

            $transliterator = \Transliterator::createFromRules(
                ':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: NFC;',
                \Transliterator::FORWARD
            );
            $normalized = $transliterator->transliterate($schema->getName());
            $name = 'Elastic' . str_replace(' ', '', ucwords(str_replace([
                '-',
                '_',
            ], ' ', $normalized)));

            $objectType = new ObjectType([
                'name' => $name,
                'fields' => fn () => $fields,
            ]);
        }

        return $objectType;
    }

    /**
     * Public URL of a file reference; absolute when the cache URL is host-relative.
     */
    private static function buildFileUrl(?string $path, array $context): ?string
    {
        $fileUrl = null;
        if ($path !== null && $path !== '') {
            $url = (string) CacheFile::from($path);
            if ($url !== '') {
                if (str_starts_with($url, '/') === true) {
                    $fileUrl = 'https://' . ($context['host'] ?? '') . $url;
                } else {
                    $fileUrl = $url;
                }
            }
        }
        return $fileUrl;
    }

    /**
     * Public URLs of a comma-separated list of file references.
     *
     * @return string[]
     */
    private static function buildFileUrls(string $raw, array $context): array
    {
        $urls = [];
        if ($raw !== '') {
            $host = $context['host'] ?? '';
            foreach (preg_split('/\s*,\s*/', $raw, -1, PREG_SPLIT_NO_EMPTY) as $path) {
                $url = (string) CacheFile::from($path);
                if ($url !== '') {
                    $urls[] = str_starts_with($url, '/') === true ? 'https://' . $host . $url : $url;
                }
            }
        }
        return $urls;
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
