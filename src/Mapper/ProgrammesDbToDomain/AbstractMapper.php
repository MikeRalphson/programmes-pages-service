<?php

namespace BBC\ProgrammesPagesService\Mapper\ProgrammesDbToDomain;

use BBC\ProgrammesPagesService\Mapper\MapperInterface;

abstract class AbstractMapper implements MapperInterface
{
    private const KEY_NOT_REQUESTED = '!';
    private const KEY_NULL = '@';
    private const KEY_NOT_CACHABLE = '&';

    protected $mapperFactory;

    protected $domainClass;

    public function __construct(MapperFactory $mapperFactory, string $className = '')
    {
        $this->mapperFactory = $mapperFactory;
        // Use voodoo to allow overriding type of domain model to a subclass
        // of the default
        if (!empty($className)) {
            if (!class_exists($className)) {
                throw new \InvalidArgumentException("$className does not exist");
            }
            if (!is_a($className, $this->domainClass, true)) {
                throw new \InvalidArgumentException("$className is not a sub-class of $this->domainClass");
            }
            $this->domainClass = $className;
        }
    }

    public function getCacheKey(array $dbEntity): string
    {
        // Every entity must have a cache key as it may be referenced by an
        // entity that uses it for caching.
        return '{' . self::KEY_NOT_CACHABLE . '}';
    }

    protected function buildCacheKey(
        array $dbEntity,
        string $primaryKey,
        array $oneToManyTuples = [],
        array $manyToManyTuples = []
    ): string {
        $cacheKey = $dbEntity[$primaryKey];

        foreach ($oneToManyTuples as $field => $mapper) {
            $cacheKey .= ',' . $this->buildCacheKeyForOneToManyTuple($dbEntity, $field, $mapper);
        }

        foreach ($manyToManyTuples as $field => $mapper) {
            $cacheKey .= ',' . $this->buildCacheKeyForManyToManyTuple($dbEntity, $field, $mapper);
        }

        return '{' . $cacheKey . '}';
    }

    private function buildCacheKeyForOneToManyTuple(
        array $dbEntity,
        string $field,
        string $mapper
    ): string {
        // Cache key shall be ! if it has not been fetched
        if (!array_key_exists($field, $dbEntity)) {
            return self::KEY_NOT_REQUESTED;
        }

        // Cache key shall be @ if it was fetched but was null
        if ($dbEntity[$field] === null) {
            return self::KEY_NULL;
        }

        // Cache key shall be the cache key of the item if it was set
        return $this->mapperFactory->{'get' . $mapper . 'Mapper'}()->getCacheKey(
            $dbEntity[$field]
        );
    }

    private function buildCacheKeyForManyToManyTuple(
        array $dbEntity,
        string $field,
        string $mapper
    ): string {
        // Cache key shall be ! if it has not been fetched
        if (!array_key_exists($field, $dbEntity)) {
            return self::KEY_NOT_REQUESTED;
        }

        // Cache key shall be an array of the cache keys of the items that were
        // set
        $cacheKeys = [];
        $mapper = $this->mapperFactory->{'get' . $mapper . 'Mapper'}();
        foreach ($dbEntity[$field] as $relatedEntity) {
            $cacheKeys[] = $mapper->getCacheKey($relatedEntity);
        }

        return '[' . implode(',', $cacheKeys) . ']';
    }
}
