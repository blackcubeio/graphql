# graphql — Schema

## Root queries (`src/Types/QueryType.php:27-152`)

| Query | Arguments | Returns | Resolver |
|---|---|---|---|
| `content(id: Int!)` | | `Content` | `ContentType::one()`: `Entities\Content::query()->andWhere(['id' => …])->one()` |
| `contents(pagination: Pagination, filters: ContentFilter)` | | `[Content!]!` | `ContentType::list()`: `ORDER BY dateCreate DESC`, filters `typeId`, `languageId`, `level`, `parentId` (direct children through `left` / `right` / `level`, empty list when the parent is unknown), then `limit` / `offset` (`:133-175`) |
| `tag(id: Int!)` | | `Tag` | `TagType::one()` |
| `tags(pagination, filters: TagFilter)` | | `[Tag!]!` | `TagType::list()`: `ORDER BY name ASC`, filters `typeId`, `level`, `parentId` |
| `menu(id: Int!)` | | `Menu` | `MenuType::one()` |
| `menus(pagination)` | | `[Menu!]!` | `MenuType::list()`: `ORDER BY left ASC` (tree order) |
| `language(id: String!)` | | `Language` | `LanguageType::one()` |
| `languages(pagination)` | | `[Language!]!` | `LanguageType::list()`: `active()`, `ORDER BY name` |
| `parameter(domain: String!, name: String!)` | | `Parameter` | `ParameterType::one()` |
| `parameters` | | `[Parameter!]!` | `ParameterType::list()`: `ORDER BY domain, name`, no pagination |
| `author(id: Int!)` | | `Author` | `AuthorType::one()`: `active()` |
| `authors(pagination)` | | `[Author!]!` | `AuthorType::list()`: `active()`, `ORDER BY lastname, firstname` |
| `search(query: String!, pagination)` | | `SearchResult!` | `SearchResultType::results()`: `SearchHelper::contentQuery()` and `tagQuery()` on the Entities, each list paginated separately; empty lists for a blank query (`src/Types/SearchResultType.php:46-77`) |

`Pagination` input: `size` (default 10), `offset` (default 0) (`src/Types/PaginationInput.php:25-58`). `ContentFilter`: `typeId`, `languageId`, `level`, `parentId`; `TagFilter`: `typeId`, `level`, `parentId`.

## Types

Dates are strings `Y-m-d H:i:s` (explicit resolvers on every date field). Scalars without resolver come from the dcore getters (`getId()`, `getName()`, `isActive()`, …).

| Type | Fields | Relations (resolver) |
|---|---|---|
| `Content` (`src/Types/ContentType.php`) | `id: Int!`, `name`, `languageId`, `typeId`, `level`, `active: Boolean!`, `dateStart`, `dateEnd`, `dateCreate`, `dateUpdate` | `slug: Slug` (`getSlugQuery()->one()`), `language: Language`, `children: [Content!]!` (rows with `left` / `right` inside the node and `level + 1`), `parent: Content` (the row containing the node at `level - 1`), `tags: [Tag!]!`, `blocs: [Bloc!]!` (pivot order), `authors: [Author!]!` (pivot order), `translations: [Content!]!`, `elastic: Elastic` |
| `Tag` (`TagType.php`) | `id: Int!`, `name: String!`, `typeId`, `level`, `active: Boolean!`, `dateCreate`, `dateUpdate` | `slug`, `children`, `parent`, `contents: [Content!]!`, `blocs`, `authors`, `elastic` |
| `Bloc` (`BlocType.php`) | `id: Int!`, `elasticSchemaId`, `active: Boolean!`, `dateCreate`, `dateUpdate` | `elastic` |
| `Menu` (`MenuType.php`) | `id: Int!`, `name: String!`, `languageId: String!`, `route`, `queryString`, `level`, `active: Boolean!`, `dateCreate`, `dateUpdate` | `children: [Menu!]!`, `parent: Menu`, `language: Language` |
| `Slug` (`SlugType.php`) | `id: Int!`, `path: String!`, `host: String` (the host name), `active: Boolean!`, `dateCreate`, `dateUpdate` | `xeo: Xeo` (`getXeoQuery()->one()`, published Entity), `jsonLd: String` (the `JsonLdBuilderInterface` of the context, `build($slugId, $host)`, JSON-encoded, `null` when empty) |
| `Xeo` (`XeoType.php`) | `title`, `description`, `image`, `noindex: Boolean!`, `nofollow: Boolean!`, `og: Boolean!`, `ogType`, `twitter: Boolean!`, `twitterCard`, `jsonldType: String!`, `keywords`, `speakable: Boolean!`, `accessibleForFree: Boolean!`, `active: Boolean!` | — |
| `Language` (`LanguageType.php`) | `id: String!`, `name: String!`, `main: Boolean!`, `active: Boolean!`, `dateCreate`, `dateUpdate` | — |
| `Parameter` (`ParameterType.php`) | `domain: String!`, `name: String!`, `value`, `dateCreate`, `dateUpdate` | — |
| `Author` (`AuthorType.php`) | `id: Int!`, `firstname: String!`, `lastname: String!`, `email`, `jobTitle`, `worksFor`, `knowsAbout`, `sameAs`, `url`, `image` (the raw path), `active: Boolean!`, `dateCreate`, `dateUpdate` | — |
| `SearchResult` (`SearchResultType.php`) | — | `contents: [Content!]!`, `tags: [Tag!]!`, by relevance |

`Xeo` `image` and `Author` `image` are storage paths, not URLs; only Elastic file fields are resolved to URLs.

## `Elastic` union (`src/Types/TypeFactory.php:118-270`)

Built once per process from every active ElasticSchema whose kind is not `Xeo`: one `ObjectType` per schema, named `Elastic` + the schema name transliterated to ASCII, `-` and `_` turned into word boundaries, CamelCased without spaces (`Hero` → `ElasticHero`, `call-out` → `ElasticCallOut`). `TypeFactory::elastic()` returns the union, or `null` when no schema qualifies; `elasticType(int $schemaId)` one member.

Each member carries `elasticSchemaId: Int!` plus one field per JSON Schema property:

| JSON Schema | GraphQL | Value |
|---|---|---|
| `string` (and unknown types) | `String` | as stored |
| `integer` | `Int` | |
| `number` | `Float` | |
| `boolean` | `Boolean` | |
| `object` | `String` | `json_encode()` of the value |
| `string` + `format: file` | `String` | `CacheFile::from($path)` URL, prefixed with `https://<request host>` when host-relative, `null` when empty |
| `string` + `format: files` | `[String]` | same for each comma-separated path |

With `fastSchema: true` (default) the properties are read from the raw JSON; with `false` the schema goes through `Swaggest\JsonSchema\Schema::import()`, a failing schema is logged and skipped. The `elastic` field of Content, Tag and Bloc resolves to `['_elasticSchemaId' => id] + getElasticValues()` when the model has a schema with a type, `null` otherwise; the union picks the member by `_elasticSchemaId`. Query with inline fragments: `elastic { ... on ElasticHero { title image } }`.
