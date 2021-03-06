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
        CacheItemPoolInterface $cache
    ) {
        parent::__construct($repository, $mapper, $cache);
    }

    public function findByPidFull(Pid $pid): ?Segment
    {
        $dbEntity = $this->repository->findByPidFull($pid);

        return $this->mapSingleEntity($dbEntity);
    }
}
