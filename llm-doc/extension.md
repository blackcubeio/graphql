# graphql — Extension

## Extension points

| Point | Where | Use |
|---|---|---|
| Route prefix, debug, fast schema | params / Laravel config, `withRoutePrefix()` on the PSR and Laravel middlewares | no code |
| Another mount | a middleware or route calling `Graphql::handle()` with a PSR-7 request | other frameworks |
| JSON-LD | bind `JsonLdBuilderInterface` to another builder: the `jsonLd` field reads it from the context | other structured data |
| Exposed fields | the dcore getters: a new `getXxx()` / `isXxx()` on an Entity subclass is readable once the field is declared in the type | application fields |
| Types and root fields | subclass `QueryType` / the `*Type` classes (their `fields` are closures) and build your own `Schema` in your own handler | mutations, new entry points, other filters |
| Elastic exposure | the ElasticSchema rows (kinds, properties, formats) | new types appear without code |
| Guards | middleware before the route (authentication, CORS, rate limit) | the package has none |

## Not extensible

| Element | Reason |
|---|---|
| The type registry | `TypeFactory` is static with a fixed method per type (`src/Types/TypeFactory.php:48-116`); `QueryType` calls those methods, not an injected factory |
| Field mapping of the Elastic types, file URL building, union naming | `private static` methods of `TypeFactory` (`:144-309`) |
| Resolvers of the static types | static methods and closures inside each `*Type` constructor |
| Ordering and pagination of the lists | hardcoded per type (`dateCreate DESC`, `name ASC`, `left ASC`, `lastname, firstname`) and the `Pagination` defaults |
| Entity layer | every resolver uses `Blackcube\Dcore\Entities\*`: no way to read unpublished rows but preview mode |
| Response status | always `200`, errors in the JSON body |
| Field resolver order (`get`, `is`, default) | `private readFieldValue()` in `Handlers\Graphql` |
