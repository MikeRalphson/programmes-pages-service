<?php

namespace BBC\ProgrammesPagesService\Service;

use BBC\ProgrammesPagesService\Data\ProgrammesDb\EntityRepository\SegmentRepository;
use BBC\ProgrammesPagesService\Domain\Entity\Segment;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Mapper\ProgrammesDbToDomain\SegmentMapper;
use Psr\Cache\CacheItemPoolInterface;

class SegmentsService extends AbstractService
{
    public function __construct(
        SegmentRepository $repository,
        SegmentMapper $mapper,
        CacheItemPoolInterface $cacheItemPoolInterface
    ) {
        parent::__construct($repository, $mapper, $cacheItemPoolInterface);
    }

    public function findByPidFull(Pid $pid): ?Segment
    {
        // get or set the result from cache
        $cacheKey = $pid;
        $mappedEntity = $this->getOrSetCache($cacheKey, 500, function () use ($pid) {
            $dbEntity = $this->repository->findByPidFull($pid);
            return $this->mapSingleEntity($dbEntity);
        });

        return $mappedEntity;
    }
}
