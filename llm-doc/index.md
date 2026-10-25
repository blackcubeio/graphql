# blackcube/graphql — LLM index

**Role:** read-only GraphQL endpoint over the published data of `blackcube/dcore`: one `POST` route, a schema built at runtime (static types for Content, Tag, Bloc, Menu, Slug, Xeo, Language, Parameter, Author, plus one `Elastic<Name>` object type per active ElasticSchema gathered in an `Elastic` union), a relevance-ranked `search`, and three ways to mount it (Yii3 config-plugin, any PSR-15 pipeline, Laravel). No mutations, no authentication.

## Package card

| | |
|---|---|
| Namespace | `Blackcube\Graphql` |
| Endpoint | `Handlers\Graphql` (PSR-15 handler), `Middlewares\PreviewMiddleware` (pass-through), `Middlewares\PsrGraphqlMiddleware` (PSR-15 mount), `Middlewares\LaravelGraphqlMiddleware` + `Laravel\GraphqlServiceProvider` |
| Types | `Types\TypeFactory` (static registry), `QueryType`, `ContentType`, `TagType`, `BlocType`, `MenuType`, `SlugType`, `XeoType`, `LanguageType`, `ParameterType`, `AuthorType`, `SearchResultType`, inputs `PaginationInput`, `ContentFilterInput`, `TagFilterInput` |
| Requires | PHP ^8.2, `blackcube/dcore` ^1.0, `blackcube/injector` ^1.0, `webonyx/graphql-php` ^15.38, `httpsoft/http-message` ^1.1, `psr/http-message` ^2.0, `psr/http-server-handler` ^1.0, `psr/http-server-middleware` ^1.0, `psr/log` ^3.0. Suggests `illuminate/http` ^12 for the Laravel pieces. |
| Yii config | config-plugin: `config/common/params.php` (`blackcube/graphql`: `routePrefix`, `debug`, `fastSchema`), `config/common/di.php` (`Graphql` with `debug` and `fastSchema`), `config/routes.php` (the POST route). Laravel: `config/laravel/blackcube-graphql.php`. |
| License | BSD-3-Clause |

## Rules

- Every resolver reads `Blackcube\Dcore\Entities\*`: published rows only, cached one hour, preview mode honoured through `Injector` (`src/Types/ContentType.php:13-16`, `SearchResultType.php:13-15`). Nothing in this package writes.
- The schema is rebuilt on each request from static caches: `TypeFactory` keeps every type and the Elastic types for the life of the process (`src/Types/TypeFactory.php:33-41`); a schema change needs `TypeFactory::reset()` in a long-running worker.
- A field without an explicit resolver is read through `get<Field>()`, then `is<Field>()`, then the graphql-php default resolver (`src/Handlers/Graphql.php:94-115`): the dcore getters are the schema.
- The answer is always HTTP 200 with a JSON body, errors included (`src/Handlers/Graphql.php:77-88`); `debug: true` adds messages and traces to the errors.
- The `elastic` field exists on Content, Tag and Bloc only when at least one active, non-Xeo ElasticSchema exists; its union member is chosen by the `_elasticSchemaId` of the values (`src/Types/TypeFactory.php:122-148`).
- File fields (`format: file` / `files`) come back as absolute `https://<request host>` URLs built by `CacheFile` of `blackcube/fileprovider` (`:275-309`): the cache directory and URL must be configured.
- `PreviewMiddleware` does nothing by itself: the preview state is read by the dcore scopes through `Injector`; the middleware only keeps the chain explicit (`src/Middlewares/PreviewMiddleware.php:27-35`).

## Parts

| File | Content | Read when |
|---|---|---|
| `schema.md` | Root queries, the static types field by field with their resolvers, filters and pagination, the `Elastic` union and its field mapping, `search`. | before writing or extending a query |
| `endpoint.md` | The handler (body, context, debug), the field resolver, the three mounts (Yii route, PSR middleware, Laravel provider and middleware), route prefix. | before mounting the endpoint |
| `integration.md` | config-plugin, container requirements, Laravel config, tests. | before plugging the package in |
| `extension.md` | What extends (types, resolvers, mounts, formats) and what does not. | before extending |
