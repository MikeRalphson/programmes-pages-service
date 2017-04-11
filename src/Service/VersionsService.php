<?php

namespace BBC\ProgrammesPagesService\Service;

use BBC\ProgrammesPagesService\Data\ProgrammesDb\EntityRepository\VersionRepository;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Mapper\ProgrammesDbToDomain\VersionMapper;
use Psr\Cache\CacheItemPoolInterface;

class VersionsService extends AbstractService
{
    public function __construct(
        VersionRepository $repository,
        VersionMapper $mapper,
        CacheItemPoolInterface $cacheItemPoolInterface
    ) {
        parent::__construct($repository, $mapper, $cacheItemPoolInterface);
    }

    public function findByPidFull(Pid $pid): ?Version
    {
        // get or set the result from cache
        $cacheKey = $pid;
        $mappedEntity = $this->getOrSetCache($cacheKey, 500, function () use ($pid) {
            $dbEntity = $this->repository->findByPidFull($pid);
            return $this->mapSingleEntity($dbEntity);
        });

        return $mappedEntity;
    }

    public function findByProgrammeItem(ProgrammeItem $programmeItem): array
    {
        $dbEntities = $this->repository->findByProgrammeItem($programmeItem->getDbId());

        return $this->mapManyEntities($dbEntities);
    }

    public function findOriginalVersionForProgrammeItem(ProgrammeItem $programmeItem): ?Version
    {
        $dbEntity = $this->repository->findOriginalVersionForProgrammeItem($programmeItem->getDbId());

        return $this->mapSingleEntity($dbEntity);
    }
}
