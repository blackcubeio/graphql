# graphql — Integration

## Split

- Package: the handler, the three mounts, the type registry, the Laravel provider and config.
- Application: a configured `blackcube/dcore` (connection, `Injector::init()`, cache, session for preview), `blackcube/fileprovider` with `cache.path` / `cache.url` (file URLs), PSR-17 factories, `JsonLdBuilderInterface`, and whatever guards the route (authentication, CORS, rate limit).

## Yii (config-plugin)

`config/configuration.php`: `params` → `common/params.php`, `di` → `common/di.php`, `routes` → `routes.php`, source directory `config`.

| Param (`blackcube/graphql`) | Default | Use |
|---|---|---|
| `routePrefix` | `/api/graphql` | path of the POST route (`config/routes.php:18`) |
| `debug` | `true` | debug messages and traces in GraphQL errors; set `false` in production |
| `fastSchema` | `true` | raw JSON properties for the Elastic types; `false` validates each schema with Swaggest first |

`config/common/di.php:17-23`: `Graphql::class` with `debug` and `fastSchema`; `ResponseFactoryInterface`, `StreamFactoryInterface`, `JsonLdBuilderInterface` (bound by the dcore config-plugin) autowired. The route carries `PreviewMiddleware` then `Graphql`.

## PSR

The container provides `PsrGraphqlMiddleware` by autowiring (`Graphql`, `PreviewMiddleware`), hence the PSR-17 factories and `JsonLdBuilderInterface`; the `blackcube/ssr` PSR definitions cover them (`docs/installation.md:57-73`).

## Laravel

`bootstrap/providers.php`: `Blackcube\Graphql\Laravel\GraphqlServiceProvider::class` after the application's Blackcube provider; `bootstrap/app.php`: `$middleware->append(LaravelGraphqlMiddleware::class)`; `php artisan vendor:publish --tag=blackcube-graphql-config` copies `config/laravel/blackcube-graphql.php` (`routePrefix`, `debug` = `APP_DEBUG`, `fastSchema`). `illuminate/http` ^12 is a `suggest`, not a requirement.

## Tests

One Codeception suite, `Integration` (`tests/Integration.suite.yml`), run with `vendor/bin/codecept run`; the database and the container come from `tests/_bootstrap.php`. `TypeFactory::reset()` clears the static type caches between tests.
