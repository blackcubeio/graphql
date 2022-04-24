Blackcube Graphql
=================

[![pipeline status](https://code.redcat.io/blackcube/graphql/badges/master/pipeline.svg)](https://code.redcat.io/blackcube/admin/commits/master)
[![coverage report](https://code.redcat.io/blackcube/graphql/badges/master/coverage.svg)](https://code.redcat.io/blackcube/admin/commits/master)

Pre-requisites
--------------

 * PHP 7.4+
   * Extension `dom`
   * Extension `fileinfo`
   * Extension `intl`
   * Extension `json`
   * Extension `mbstring`
 * Apache or NginX
 * Blackcube core

Pre-flight
----------

Add blackcube graphql to the project

```
composer require "blackcube/graphql" 
```
   
Installation
------------

> **Beware**: `Blackcube graphql` can only be installed if `Blackcube core` is already set up 


### Inject Blackcube admin in application

```php 
// main configuration file
// ...
    'bootstrap' => [
        // ... boostrapped modules
        'blackcube', // blackcube core
        'gql', // blackcube graphql
    ],
    'modules' => [
        // ... other modules
        'blackcube' => [
            'class' => blackcube\core\Module::class,
        ],
        'gql' => [
            'class' => blackcube\graphql\Module::class,
        ],
    ],
// ...
```

> Blackcube graphql is now ready, you can access it through `https://host.domain/gql`
