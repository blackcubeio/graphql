# Installation

```bash
composer require blackcube/graphql
```

## Requirements

- PHP 8.4+
- `blackcube/dcore ^1.0`
- `webonyx/graphql-php ^15.33`
- `psr/http-message ^2.0`
- `psr/http-server-handler ^1.0`
- `psr/http-server-middleware ^1.0`
- `httpsoft/http-message ^1.1`

## Configuration

The package uses `config-plugin` for automatic Yii3 registration:

| Config file | Content |
|---|---|
| `config/common/params.php` | Package parameters |
| `config/common/di.php` | DI container (Graphql handler) |
| `config/routes.php` | POST route |

### Parameters

```php
'blackcube/graphql' => [
    'routePrefix' => '/api/graphql',   // endpoint path
    'debug' => true,                   // include debug messages and stack traces in errors
    'fastSchema' => true,              // bypass Swaggest Schema::import for elastic types
],
```

| Param | Default | Description |
|---|---|---|
| `routePrefix` | `/api/graphql` | Route path for the GraphQL endpoint |
| `debug` | `true` | Include debug messages and stack traces in errors |
| `fastSchema` | `true` | Skip JSON Schema validation, use raw JSON properties for elastic types |

### DI bindings

The `Graphql` handler is wired with `debug` and `fastSchema` from params. PSR factories (`ResponseFactoryInterface`, `StreamFactoryInterface`) and `JsonLdBuilderInterface` are auto-wired.

### Route

A single POST route is registered:

```
POST {routePrefix} → PreviewMiddleware → Graphql handler
```

Route name: `graphql`.

## Slim / PSR

The package ships a PSR-15 middleware: it answers POST requests on the route prefix and hands every other request over.

```php
use Blackcube\Graphql\Middlewares\PsrGraphqlMiddleware;

$app->add($container->get(PsrGraphqlMiddleware::class));
```

The default route prefix is `/api/graphql`; move it with `withRoutePrefix()`:

```php
$app->add($container->get(PsrGraphqlMiddleware::class)->withRoutePrefix('/gql'));
```

The container must provide the PSR-17 factories (`ResponseFactoryInterface`, `StreamFactoryInterface`) and `JsonLdBuilderInterface`; the `blackcube/ssr` PSR definitions already do.

## Laravel

Register the service provider and append the middleware:

```php
// bootstrap/providers.php
return [
    App\Providers\BlackcubeServiceProvider::class,
    Blackcube\Graphql\Laravel\GraphqlServiceProvider::class,
];

// bootstrap/app.php
$middleware->append(LaravelGraphqlMiddleware::class);
```

Configuration lives in `config/blackcube-graphql.php` (routePrefix, debug, fastSchema):

```bash
php artisan vendor:publish --tag=blackcube-graphql-config
```
