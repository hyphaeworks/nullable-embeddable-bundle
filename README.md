# Nullable Embeddable Bundle

[![CI](https://github.com/hyphaeworks/nullable-embeddable-bundle/actions/workflows/ci.yml/badge.svg)](https://github.com/hyphaeworks/nullable-embeddable-bundle/actions/workflows/ci.yml)

A [Symfony bundle](https://symfony.com/doc/current/bundles.html) for nullable [Doctrine embeddables](https://www.doctrine-project.org/projects/doctrine-orm/en/current/tutorials/embeddables.html).
Doctrine always creates an embeddable object on load, even when all its columns are `NULL`.
This bundle sets such an empty embeddable to `null` instead.

## Installation

```bash
composer require hyphaeworks/nullable-embeddable-bundle
```

Register the bundle in `config/bundles.php` if Symfony Flex did not do it for you. No configuration is needed.

```php
Hyphaeworks\NullableEmbeddableBundle\NullableEmbeddableBundle::class => ['all' => true],
```

| Package                  | Versions      |
|--------------------------|---------------|
| PHP                      | 8.2 to 8.5    |
| Symfony                  | 6.4, 7.4, 8.x |
| doctrine/orm             | 2.17+, 3.x    |
| doctrine/doctrine-bundle | 2.12+, 3.x    |

## Usage

Mark the entity with `#[ContainsNullableEmbeddable]` and give the embedded property a nullable type:

```php
use Doctrine\ORM\Mapping as ORM;
use Hyphaeworks\NullableEmbeddableBundle\Attribute\ContainsNullableEmbeddable;

#[ORM\Entity]
#[ContainsNullableEmbeddable]
class User
{
    #[ORM\Embedded(class: EmailAddress::class)]
    private ?EmailAddress $emailAddress = null;
}
```

An untyped property needs the `#[NullableEmbedded]` attribute instead:

```php
#[ORM\Embedded(class: EmailAddress::class)]
#[NullableEmbedded]
private $emailAddress;
```

Embedded properties with a non-nullable type are left alone.
With XML mapping, the marker attribute still goes on the entity class.

## Rules

- An embeddable is empty when every property is `null` or uninitialized.
- Nested embeddables are supported: an outer embeddable that only holds an empty inner one becomes `null` too.
- A nullable embedded property must not be `readonly`, because the bundle has to set it to `null` after Doctrine.
  Loading such an entity throws a `ReadonlyEmbeddedPropertyException`. The properties inside the embeddable may be `readonly`.
- Single-column value objects (an email address, a currency code) are often better served by a
  [custom DBAL type](https://www.doctrine-project.org/projects/doctrine-orm/en/current/cookbook/custom-mapping-types.html).

## Metadata cache

The bundle hooks into marked entities through Doctrine's class metadata, so the hook is stored in the metadata cache.
Default Symfony setups rebuild that cache on deploy. If yours lives in a persistent store (Redis, Memcached,
a database), clear it after installing or upgrading the bundle and after adding `#[ContainsNullableEmbeddable]`
to an entity, or empty embeddables are silently kept:

```bash
bin/console doctrine:cache:clear-metadata
```

## Performance

Entities without the marker are not affected. Marked entities cost about 1µs more per loaded entity.

## License

MIT. See [LICENSE](LICENSE).
