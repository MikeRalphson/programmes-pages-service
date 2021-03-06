<?php

namespace BBC\ProgrammesPagesService\Mapper\ProgrammesDbToDomain;

use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\Unfetched\UnfetchedProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Exception\DataNotFetchedException;

use DateTimeImmutable;

class CollapsedBroadcastMapper extends AbstractMapper
{
    public function getDomainModel(array $dbCollapsedBroadcast, array $services = []): CollapsedBroadcast
    {
        return new CollapsedBroadcast(
            $this->getProgrammeItemModel($dbCollapsedBroadcast),
            $this->getServiceModels($services, $dbCollapsedBroadcast),
            DateTimeImmutable::createFromMutable($dbCollapsedBroadcast['startAt']),
            DateTimeImmutable::createFromMutable($dbCollapsedBroadcast['endAt']),
            $dbCollapsedBroadcast['duration'],
            $dbCollapsedBroadcast['isBlanked'],
            $dbCollapsedBroadcast['isRepeat']
        );
    }

    private function getProgrammeItemModel(array $dbCollapsedBroadcast, string $key = 'programmeItem'): Programme
    {
        // It is not valid for a CollapsedBroadcast to have no programmeItem
        // so it counts as Unfetched even if the key exists but is null
        if (isset($dbCollapsedBroadcast[$key])) {
            return $this->mapperFactory->getProgrammeMapper()->getDomainModel($dbCollapsedBroadcast[$key]);
        }

        return new UnfetchedProgrammeItem();
    }

    private function getServiceModels(
        array $services,
        array $dbCollapsedBroadcast,
        string $key = 'serviceIds'
    ): array {
        if (!array_key_exists($key, $dbCollapsedBroadcast) ||
            !is_array($dbCollapsedBroadcast[$key]) ||
            empty($dbCollapsedBroadcast[$key])
        ) {
            throw new DataNotFetchedException('All CollapsedBroadcasts must be joined to at least one Service');
        }

        $serviceModels = [];
        foreach ($dbCollapsedBroadcast[$key] as $sid) {
            if (isset($services[$sid])) {
                $serviceModels[] = $this->mapperFactory->getServiceMapper()->getDomainModel($services[$sid]);
            }
        }

        return $serviceModels;
    }
}
