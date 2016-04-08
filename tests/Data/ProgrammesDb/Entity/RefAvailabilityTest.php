<?php

namespace Tests\BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity;

use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Episode;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\RefAvailability;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\RefMediaSet;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\Entity\Version;
use BBC\ProgrammesPagesService\Domain\Enumeration\AvailabilityStatusEnum;
use PHPUnit_Framework_TestCase;
use DateTime;
use InvalidArgumentException;

class AvailabilityTest extends PHPUnit_Framework_TestCase
{
    public function testDefaults()
    {
        $entity = new RefAvailability();

        $this->assertSame(null, $entity->getId());
        $this->assertSame(AvailabilityStatusEnum::PENDING, $entity->getStatus());
        $this->assertSame(null, $entity->getVersion());
        $this->assertSame(null, $entity->getScheduledStart());
        $this->assertSame(null, $entity->getScheduledEnd());
        $this->assertSame(null, $entity->getActualStart());
    }

    /**
     * @dataProvider setterDataProvider
     */
    public function testSetters($name, $validValue)
    {
        $entity = new RefAvailability();

        $entity->{'set' . $name}($validValue);
        $this->assertSame($validValue, $entity->{'get' . $name}());
    }

    public function setterDataProvider()
    {
        return [
            ['Status', AvailabilityStatusEnum::AVAILABLE],
            ['Version', new Version()],
            ['ScheduledStart', new DateTime()],
            ['ScheduledEnd', new DateTime()],
            ['ActualStart', new DateTime()],
            ['MediaSet', new RefMediaSet()],
            ['Type', 'audio_nondrm_download'],
            ['ProgrammeItem', new Episode()],
        ];
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Called setStatus with an invalid value. Expected one of "available", "future" or "pending" but got "garbage"
     */
    public function testUnknownStatusThrowsException()
    {
        $entity = new RefAvailability();

        $entity->setStatus('garbage');
    }
}