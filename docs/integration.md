# Integration

## PSR / generic PHP

The package is built on PSR interfaces:

| Interface | Implementation | Purpose |
|---|---|---|
| `RequestHandlerInterface` (PSR-15) | `Graphql` | Handles GraphQL POST requests |
| `MiddlewareInterface` (PSR-15) | `PreviewMiddleware` | Preview mode injection |
| `MiddlewareInterface` (PSR-15) | `PsrGraphqlMiddleware` | Answers POST on the route prefix, hands everything else over |
| `ResponseFactoryInterface` (PSR-17) | (auto-wired) | HTTP response creation |
| `StreamFactoryInterface` (PSR-17) | (auto-wired) | Stream creation for response body |

`PsrGraphqlMiddleware` wraps `PreviewMiddleware` and the `Graphql` handler behind a single middleware to add to the stack (Slim, Mezzio, any PSR-15 pipeline). See [installation](installation.md) for the wiring.

### Custom field resolver

The `Graphql` handler uses a custom field resolver that follows PHP getter conventions:

1. Try `get{FieldName}()` on the source object
2. Try `is{FieldName}()` on the source object
3. Fall back to graphql-php default resolver

This allows GraphQL fields to resolve directly from dcore model getters without explicit resolvers.

### Preview middleware

`PreviewMiddleware` is a pass-through middleware. The `PreviewManager` is resolved via Injector directly in `ScopedQuery` — the middleware ensures it is in the middleware chain for proper DI resolution.

When preview mode is active, `publishable()` and `available()` scopes are relaxed, making unpublished content visible through GraphQL.

## Yii

### Config-plugin

The package auto-registers via `config-plugin`:

| Key | File | Content |
|---|---|---|
| `params` | `config/common/params.php` | routePrefix, debug, fastSchema |
| `di` | `config/common/di.php` | Graphql handler wiring |
| `routes` | `config/routes.php` | POST route registration |

### Route

A single POST route is registered with the Yii router:

```php
Route::post($routePrefix)
    ->middleware(PreviewMiddleware::class)
    ->action(Graphql::class)
    ->name('graphql')
```

### Schema caching

Elastic type building queries `ElasticSchema` once per request. Schema caching is handled globally via `BaseElastic` in dcore — one query for all elastic schemas across all entity types.

## Laravel

`GraphqlServiceProvider` registers the `Graphql` handler and `LaravelGraphqlMiddleware`, with `singletonIf` bindings for the PSR-17 factories (HttpSoft) and `JsonLdBuilderInterface`. The middleware converts the incoming request to PSR-7, delegates to the handler, and converts the response back.

| Key | File | Content |
|---|---|---|
| provider | `bootstrap/providers.php` | `Blackcube\Graphql\Laravel\GraphqlServiceProvider` |
| middleware | `bootstrap/app.php` | `$middleware->append(LaravelGraphqlMiddleware::class)` |
| config | `config/blackcube-graphql.php` | routePrefix, debug, fastSchema |

The `demo-laravel` and `demo-slim` applications wire both integrations and serve as reference implementations.
