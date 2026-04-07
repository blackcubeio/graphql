# Schema — GraphQL type reference

## Root queries

| Query | Args | Returns |
|---|---|---|
| `content(id: Int!)` | ID | `Content` |
| `contents(pagination, filters)` | Pagination + ContentFilter | `[Content!]!` |
| `tag(id: Int!)` | ID | `Tag` |
| `tags(pagination, filters)` | Pagination + TagFilter | `[Tag!]!` |
| `menu(id: Int!)` | ID | `Menu` |
| `menus(pagination)` | Pagination | `[Menu!]!` |
| `language(id: String!)` | ID | `Language` |
| `languages(pagination)` | Pagination | `[Language!]!` |
| `parameter(domain: String!, name: String!)` | Domain + Name | `Parameter` |
| `parameters` | — | `[Parameter!]!` |
| `author(id: Int!)` | ID | `Author` |
| `authors(pagination)` | Pagination | `[Author!]!` |

## Types

### Content

| Field | Type | Description |
|---|---|---|
| `id` | `Int!` | |
| `name` | `String` | |
| `languageId` | `String` | |
| `typeId` | `Int` | |
| `level` | `Int` | Tree level (1 = root) |
| `active` | `Boolean!` | |
| `dateStart` | `String` | Publication start (Y-m-d H:i:s) |
| `dateEnd` | `String` | Publication end |
| `dateCreate` | `String` | |
| `dateUpdate` | `String` | |
| `slug` | `Slug` | Associated URL |
| `language` | `Language` | |
| `children` | `[Content!]!` | Direct children in tree |
| `parent` | `Content` | Direct parent in tree |
| `tags` | `[Tag!]!` | Associated tags |
| `blocs` | `[Bloc!]!` | Ordered blocs |
| `authors` | `[Author!]!` | Ordered authors |
| `translations` | `[Content!]!` | Other translations |
| `elastic` | `Elastic` | Dynamic properties (union type) |

### Tag

| Field | Type | Description |
|---|---|---|
| `id` | `Int!` | |
| `name` | `String!` | |
| `typeId` | `Int` | |
| `level` | `Int` | Tree level |
| `active` | `Boolean!` | |
| `dateCreate` | `String` | |
| `dateUpdate` | `String` | |
| `slug` | `Slug` | Associated URL |
| `children` | `[Tag!]!` | Direct children in tree |
| `parent` | `Tag` | Direct parent |
| `contents` | `[Content!]!` | Contents tagged with this tag |
| `blocs` | `[Bloc!]!` | Ordered blocs |
| `authors` | `[Author!]!` | Ordered authors |
| `elastic` | `Elastic` | Dynamic properties |

### Bloc

| Field | Type | Description |
|---|---|---|
| `id` | `Int!` | |
| `elasticSchemaId` | `Int` | |
| `active` | `Boolean!` | |
| `dateCreate` | `String` | |
| `dateUpdate` | `String` | |
| `elastic` | `Elastic` | Dynamic properties |

### Slug

| Field | Type | Description |
|---|---|---|
| `id` | `Int!` | |
| `path` | `String!` | URL path |
| `host` | `String` | Host domain name |
| `active` | `Boolean!` | |
| `dateCreate` | `String` | |
| `dateUpdate` | `String` | |
| `xeo` | `Xeo` | SEO metadata |
| `jsonLd` | `String` | JSON-LD structured data (JSON-encoded) |

The `jsonLd` field uses `JsonLdBuilderInterface` from the request context.

### Xeo

| Field | Type | Description |
|---|---|---|
| `title` | `String` | Page title |
| `description` | `String` | Meta description |
| `image` | `String` | Image path |
| `noindex` | `Boolean!` | |
| `nofollow` | `Boolean!` | |
| `og` | `Boolean!` | Open Graph enabled |
| `ogType` | `String` | OG type |
| `twitter` | `Boolean!` | Twitter Cards enabled |
| `twitterCard` | `String` | Card type |
| `jsonldType` | `String!` | schema.org type |
| `keywords` | `String` | |
| `speakable` | `Boolean!` | |
| `accessibleForFree` | `Boolean!` | |
| `active` | `Boolean!` | |

### Menu

| Field | Type | Description |
|---|---|---|
| `id` | `Int!` | |
| `name` | `String!` | |
| `languageId` | `String!` | |
| `route` | `String` | |
| `queryString` | `String` | |
| `level` | `Int` | Tree level |
| `active` | `Boolean!` | |
| `dateCreate` | `String` | |
| `dateUpdate` | `String` | |
| `children` | `[Menu!]!` | Direct children |
| `parent` | `Menu` | Direct parent |
| `language` | `Language` | |

### Language

| Field | Type |
|---|---|
| `id` | `String!` |
| `name` | `String!` |
| `main` | `Boolean!` |
| `active` | `Boolean!` |
| `dateCreate` | `String` |
| `dateUpdate` | `String` |

### Parameter

| Field | Type |
|---|---|
| `domain` | `String!` |
| `name` | `String!` |
| `value` | `String` |
| `dateCreate` | `String` |
| `dateUpdate` | `String` |

### Author

| Field | Type |
|---|---|
| `id` | `Int!` |
| `firstname` | `String!` |
| `lastname` | `String!` |
| `email` | `String` |
| `jobTitle` | `String` |
| `worksFor` | `String` |
| `knowsAbout` | `String` |
| `sameAs` | `String` |
| `url` | `String` |
| `image` | `String` |
| `active` | `Boolean!` |
| `dateCreate` | `String` |
| `dateUpdate` | `String` |

## Input types

### PaginationInput

| Field | Type | Default |
|---|---|---|
| `size` | `Int` | 10 |
| `offset` | `Int` | 0 |

```graphql
contents(pagination: { size: 10, offset: 20 }) { ... }
```

### ContentFilterInput

| Field | Type | Description |
|---|---|---|
| `typeId` | `Int` | Filter by type |
| `languageId` | `String` | Filter by language |
| `level` | `Int` | Filter by tree level |
| `parentId` | `Int` | Filter by parent (direct children) |

```graphql
contents(filters: { typeId: 1, languageId: "fr", level: 1 }) { ... }
```

### TagFilterInput

| Field | Type | Description |
|---|---|---|
| `typeId` | `Int` | Filter by type |
| `level` | `Int` | Filter by tree level |
| `parentId` | `Int` | Filter by parent |

```graphql
tags(filters: { typeId: 2, level: 1, parentId: 3 }) { ... }
```

## Elastic types (dynamic)

Each active `ElasticSchema` (except kind `Xeo`) generates a GraphQL `ObjectType` at runtime. All elastic types are grouped in an `Elastic` `UnionType`, available as the `elastic` field on Content, Tag, and Bloc.

### Naming

Schema name is normalized: `"Elastic"` + transliterated name with spaces removed. For example, schema `"Hero"` becomes `ElasticHero`.

### Field type mapping

| JSON Schema | GraphQL |
|---|---|
| `string` | `String` |
| `integer` | `Int` |
| `number` | `Float` |
| `boolean` | `Boolean` |
| `object` | `String` (JSON-encoded) |
| `string` + `format: file` | `String` (resolved to absolute URL via CacheFile) |
| `string` + `format: files` | `[String]` (comma-separated, resolved to URLs) |

Every elastic type also includes an `elasticSchemaId: Int!` field.

### Fast schema mode

With `fastSchema: true` (default), elastic type building uses raw JSON properties directly instead of validating through Swaggest JSON Schema. This is faster and sufficient for runtime type generation.

### Example query

```graphql
{
  content(id: 1) {
    id
    name
    slug { path }
    language { id name }
    tags { id name }
    blocs {
      id
      elastic {
        ... on ElasticHero {
          title
          image
          description
        }
      }
    }
    elastic {
      ... on ElasticHero {
        title
        image
      }
    }
  }
}
```

## TypeFactory

Singleton factory providing cached instances of all GraphQL types.

| Method | Returns |
|---|---|
| `TypeFactory::content()` | `ContentType` |
| `TypeFactory::tag()` | `TagType` |
| `TypeFactory::slug()` | `SlugType` |
| `TypeFactory::xeo()` | `XeoType` |
| `TypeFactory::bloc()` | `BlocType` |
| `TypeFactory::language()` | `LanguageType` |
| `TypeFactory::parameter()` | `ParameterType` |
| `TypeFactory::author()` | `AuthorType` |
| `TypeFactory::menu()` | `MenuType` |
| `TypeFactory::query()` | `QueryType` |
| `TypeFactory::pagination()` | `PaginationInput` |
| `TypeFactory::contentFilter()` | `ContentFilterInput` |
| `TypeFactory::tagFilter()` | `TagFilterInput` |
| `TypeFactory::elastic()` | `?UnionType` |
| `TypeFactory::elasticType(int $schemaId)` | `?ObjectType` |
| `TypeFactory::setFastSchema(bool)` | `void` |
| `TypeFactory::reset()` | `void` (clear cache, for tests) |
