# graphql — Endpoint and mounts

## `Handlers\Graphql` (`src/Handlers/Graphql.php`)

Constructor `(ResponseFactoryInterface $responseFactory, StreamFactoryInterface $streamFactory, JsonLdBuilderInterface $jsonLdBuilder, bool $debug = false, bool $fastSchema = true)` (`:36-42`). `handle()` (`:44-89`):

1. body = the parsed body when it is an array, else `json_decode()` of the raw body (`[]` when invalid); `query` (default `''`) and `variables` (default `null`) read from it;
2. `TypeFactory::setFastSchema($fastSchema)`, `new Schema(['query' => TypeFactory::query()])`;
3. `GraphQL::executeQuery($schema, $query, null, $context, $variables, null, readFieldValue)` with `$context = ['request' => ServerRequestInterface, 'host' => the request URI host, 'jsonLdBuilder' => JsonLdBuilderInterface]`;
4. `toArray()` with `DebugFlag::INCLUDE_DEBUG_MESSAGE | INCLUDE_TRACE` when `$debug`, `DebugFlag::NONE` otherwise;
5. `200` `application/json`, JSON with unescaped slashes and unicode. A GraphQL error never changes the status code.

`readFieldValue()` (`:94-115`): on an object source, `get<FieldName>()` if it exists, else `is<FieldName>()`, else `Executor::defaultFieldResolver()` (array keys, public properties). Field names are therefore the dcore getter names without their prefix (`dateStart` → `getDateStart()`, `active` → `isActive()`).

## Mounts

| Host | Wiring |
|---|---|
| Yii3 | `config/routes.php`: `Route::post($params['blackcube/graphql']['routePrefix'] ?? '/api/graphql')->middleware(PreviewMiddleware::class)->action(Graphql::class)->name('graphql')` (`:18-21`); `config/common/di.php` builds `Graphql` with `debug` and `fastSchema` from the params, the PSR-17 factories and `JsonLdBuilderInterface` autowired |
| PSR-15 (Slim, Mezzio, …) | `Middlewares\PsrGraphqlMiddleware(Graphql $graphqlHandler, PreviewMiddleware $previewMiddleware)`: answers `POST` on the route prefix (`/api/graphql` by default, `withRoutePrefix(string)` returns a clone with another), hands every other request to the next handler (`src/Middlewares/PsrGraphqlMiddleware.php:29-58`); `$app->add($container->get(PsrGraphqlMiddleware::class)->withRoutePrefix('/gql'))` |
| Laravel | `Laravel\GraphqlServiceProvider`: merges `config/laravel/blackcube-graphql.php` under `blackcube-graphql`, binds `singletonIf` the HttpSoft PSR-17 factories and `JsonLdBuilderInterface` → `JsonLdBuilder`, a `Graphql` singleton with `debug` / `fastSchema` from the config, a `LaravelGraphqlMiddleware` singleton on `routePrefix`; `boot()` publishes the config with the tag `blackcube-graphql-config` (`src/Laravel/GraphqlServiceProvider.php:33-60`). `Middlewares\LaravelGraphqlMiddleware::handle(Request, Closure)`: on `POST` + prefix, converts the Laravel request to a HttpSoft PSR-7 request (method, full URL, `$_SERVER`, query, body, headers), calls the handler, copies status, body and headers into a Symfony `Response`; otherwise `$next($request)` (`src/Middlewares/LaravelGraphqlMiddleware.php:47-72`) |

`Middlewares\PreviewMiddleware` is a pass-through (`src/Middlewares/PreviewMiddleware.php:27-35`): the preview state reaches the queries through `Injector` and the dcore scopes, the middleware only documents the dependency in the chain.

## Request shape

```http
POST /api/graphql
Content-Type: application/json

{"query": "{ contents(filters: {languageId: \"fr\"}) { id name slug { path } } }", "variables": null}
```

No authentication, no CSRF handling, no CORS in the package: the application wraps the route.
