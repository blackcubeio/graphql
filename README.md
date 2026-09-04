# Blackcube GraphQL

Read-only GraphQL API for Blackcube CMS. Exposes Content, Tag, Menu, Language, Parameter, Author with their relations and elastic data, plus relevance-ranked full-text search. No mutations — query API for headless frontends.

[![License](https://img.shields.io/badge/license-BSD--3--Clause-blue.svg)](LICENSE.md)
[![Packagist Version](https://img.shields.io/packagist/v/blackcube/graphql.svg)](https://packagist.org/packages/blackcube/graphql)

## Quickstart

```bash
composer require blackcube/graphql
```

```graphql
{
  contents(filters: { languageId: "fr" }) {
    id
    name
    slug { path }
    blocs {
      elastic {
        ... on ElasticHero { title image }
      }
    }
  }
}
```

Full-text search across published Contents and Tags, ranked by relevance:

```graphql
{
  search(query: "newsletter", pagination: { size: 10 }) {
    contents { id name slug { path } }
    tags { id name }
  }
}
```

## Integration

| Host | Wiring |
|---|---|
| Yii3 | auto-registered via config-plugin, POST route on `routePrefix` |
| Slim / PSR | `$app->add($container->get(PsrGraphqlMiddleware::class))` |
| Laravel | register `GraphqlServiceProvider`, `$middleware->append(LaravelGraphqlMiddleware::class)` |

## Documentation

- [Installation](docs/installation.md) — requirements, configuration, Yii/Slim/Laravel wiring
- [Schema](docs/schema.md) — root queries, types, filters, pagination, elastic types
- [Integration](docs/integration.md) — PSR, Yii and Laravel integration

## License

BSD-3-Clause. See [LICENSE.md](LICENSE.md).
