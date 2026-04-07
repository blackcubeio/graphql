# Blackcube GraphQL

Read-only GraphQL API for Blackcube CMS. Exposes Content, Tag, Menu, Language, Parameter, Author with their relations and elastic data. No mutations — query API for headless frontends.

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

## Documentation

- [Installation](docs/installation.md) — requirements, configuration, route
- [Schema](docs/schema.md) — root queries, types, filters, pagination, elastic types
- [Integration](docs/integration.md) — PSR and Yii integration

## License

BSD-3-Clause. See [LICENSE.md](LICENSE.md).
