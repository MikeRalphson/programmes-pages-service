<?php

namespace BBC\ProgrammesPagesService\Service;

use BBC\ProgrammesPagesService\Mapper\MapperInterface;
use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use Doctrine\ORM\EntityRepository;
use Psr\Cache\CacheItemPoolInterface;

abstract class AbstractService
{
    public const DEFAULT_PAGE = 1;
    public const DEFAULT_LIMIT = 300;
    public const NO_LIMIT = null;

    /**
     * @var MapperInterface
     */
    protected $mapper;

    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * @var CacheItemPoolInterface
     */
    private $cacheItemPoolInterface;

    public function __construct(
        EntityRepository $repository,
        MapperInterface $mapper,
        CacheItemPoolInterface $cacheItemPoolInterface
    ) {
        $this->repository = $repository;
        $this->mapper = $mapper;
        $this->cacheItemPoolInterface = $cacheItemPoolInterface;
    }

    protected function getOffset(?int $limit, int $page): int
    {
        if ($page < 1) {
            throw new InvalidArgumentException('Page should be greater than 0 but got ' . $page);
        }

        if ($limit === self::NO_LIMIT && $page !== 1) {
            throw new InvalidArgumentException('Page greater than 1 with no limit? Are you sure?');
        }

        return $limit * ($page - 1);
    }

    protected function mapSingleEntity(?array $dbEntity, ...$additionalArgs)
    {
        if (is_null($dbEntity)) {
            return null;
        }

        return $this->mapper->getDomainModel($dbEntity, ...$additionalArgs);
    }

    protected function mapManyEntities(array $dbEntities, ...$additionalArgs): array
    {
        $mappedEntities = [];
        foreach ($dbEntities as $dbEntity) {
            $mappedEntities[] = $this->mapSingleEntity($dbEntity, ...$additionalArgs);
        }
        return $mappedEntities;
    }

    /**
     * Get and item from cache, if the item is not cached yet, add the item into the cache.
     *
     * @param string $key
     * @param int $ttl amount of time in seconds between when that item is stored and it is considered stale
     * @param $dataOrFunction mixed|\Closure data to be stored, if and anonymous function is sent,
     * it will be executed and the returned data will be stored
     * @return mixed
     */
    public function getOrSetCache(string $key, int $ttl, $dataOrFunction)
    {
        // generate the full key name. no need to worry about key names length
        // pattern: FullClassPath_FunctionName_UniqueIdentifier1_UniqueIdentifierN
        // valid example: BBC_ProgrammesPagesService_Service_AbstractService_findUpcomingByProgramme_b006q2x0_30_1
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $key = get_class() .'_'. $backtrace[1]['function'] .'_'. $key;
        // replace the following reserved characters {}()/\@:
        $key = preg_replace('/[\\\\{}\/()@:]/', '_', $key);

        // instantiate item objects
        $item = $this->cacheItemPoolInterface->getItem($key);

        // get the item value for this key, null value returned if key doesn't exist on the cache server
        $cachedValue = $item->get();

        // if key doesn't exist yet, get the data and store in memory.
        if (is_null($cachedValue)) {
            // get the data to be stored and returned
            if (is_callable($dataOrFunction)) {
                $data = $dataOrFunction();
            } else {
                $data = $dataOrFunction;
            }

            // store in memory if data is not null or empty
            if (!empty($data)) {
                $item->set($data);
                $item->expiresAfter($ttl);
                $this->cacheItemPoolInterface->save($item);
            }
        } else {
            // if there is a value for this key, return the value
            $data = $cachedValue;
        }

        return $data;
    }
}
