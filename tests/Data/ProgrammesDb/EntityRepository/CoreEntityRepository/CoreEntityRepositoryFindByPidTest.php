<?php

namespace Tests\BBC\ProgrammesPagesService\Data\ProgrammesDb\EntityRepository\CoreEntityRepository;

use Tests\BBC\ProgrammesPagesService\AbstractDatabaseTest;
use BBC\ProgrammesPagesService\Data\ProgrammesDb\EntityRepository\CoreEntityRepository;

/**
 * @covers BBC\ProgrammesPagesService\Data\ProgrammesDb\EntityRepository\CoreEntityRepository::<public>
 */
class CoreEntityRepositoryFindByPidTest extends AbstractDatabaseTest
{
    public function testFindByPidFull()
    {
        $this->loadFixtures(['MongrelsFixture']);
        $repo = $this->getEntityManager()->getRepository('ProgrammesPagesService:CoreEntity');

        $entity = $repo->findByPidFull('b00swyx1');
        $this->assertInternalType('array', $entity);
        $this->assertEquals('b00swyx1', $entity['pid']);

        // findByPid query and the parent lookup query
        $this->assertCount(2, $this->getDbQueries());
    }

    public function testFindByPidFullWhenEmptyResult()
    {
        $this->loadFixtures(['MongrelsFixture']);
        $repo = $this->getEntityManager()->getRepository('ProgrammesPagesService:CoreEntity');

        $entity = $repo->findByPidFull('qqqqqqq');
        $this->assertNull($entity);

        // findByPid query only
        $this->assertCount(1, $this->getDbQueries());
    }
}