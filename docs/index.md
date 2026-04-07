# graphql — Read-only GraphQL API

Read-only GraphQL API for Blackcube CMS. Exposes Content, Tag, Menu, Language, Parameter, Author with their relations and elastic data.

No mutations. One query per request. Preview-aware.

## Architecture

```
POST /api/graphql → PreviewMiddleware → Graphql Handler → JSON response
```

1. Route receives a POST with `{ query, variables }`
2. PreviewMiddleware injects preview context into ScopedQuery
3. Graphql handler builds the schema, executes the query, returns JSON

## Components

| Component | Purpose |
|---|---|
| `Graphql` | PSR-15 handler. Builds schema, executes query, returns JSON |
| `PreviewMiddleware` | PSR-15 middleware. Integrates dcore preview mode |
| `TypeFactory` | Singleton factory for all GraphQL types (lazy-loaded) |
| `QueryType` | Root query with 12 entry points |

## Documentation

- [Installation](installation.md) — requirements, configuration, route
- [Schema](schema.md) — root queries, types, filters, pagination, elastic types
- [Integration](integration.md) — PSR and Yii integration
