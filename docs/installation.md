# Installation

```bash
composer require blackcube/graphql
```

## Requirements

- PHP 8.3+
- `blackcube/dcore ^1.0`
- `webonyx/graphql-php ^15.30`
- `psr/http-message ^2.0`
- `psr/http-server-handler ^1.0`
- `psr/http-server-middleware ^1.0`

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
    'debug' => false,                  // include debug messages and stack traces in errors
    'fastSchema' => true,              // bypass Swaggest Schema::import for elastic types
],
```

| Param | Default | Description |
|---|---|---|
| `routePrefix` | `/api/graphql` | Route path for the GraphQL endpoint |
| `debug` | `false` | Include debug messages and stack traces in errors |
| `fastSchema` | `true` | Skip JSON Schema validation, use raw JSON properties for elastic types |

### DI bindings

The `Graphql` handler is wired with `debug` and `fastSchema` from params. PSR factories (`ResponseFactoryInterface`, `StreamFactoryInterface`) and `JsonLdBuilderInterface` are auto-wired.

### Route

A single POST route is registered:

```
POST {routePrefix} → PreviewMiddleware → Graphql handler
```

Route name: `graphql`.
