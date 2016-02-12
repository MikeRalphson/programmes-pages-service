<?php

\Gedmo\DoctrineExtensions::registerAnnotations();
// add our custom types
\Doctrine\DBAL\Types\Type::addType('date_partial', 'BBC\ProgrammesPagesService\Data\ProgrammesDb\Type\DatePartialType');

$cachedAnnotationReader = new \Doctrine\Common\Annotations\CachedReader(
    new \Doctrine\Common\Annotations\AnnotationReader(),
    new \Doctrine\Common\Cache\ArrayCache()
);

$evm = new \Doctrine\Common\EventManager();

// tree event subscriber
$treeListener = new \Gedmo\Tree\TreeListener();
$treeListener->setAnnotationReader($cachedAnnotationReader);
$evm->addEventSubscriber($treeListener);

// timestampable event subscriber
$timestampableListener = new \Gedmo\Timestampable\TimestampableListener();
$timestampableListener->setAnnotationReader($cachedAnnotationReader);
$evm->addEventSubscriber($timestampableListener);


$conn = [
    'driver' => 'pdo_sqlite',
    'path' => __DIR__ . '/db.sqlite',
];
$config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration([__DIR__ . "/../src/Data/ProgrammesDb/Entity"], true, null, null, false);

// obtaining the entity manager
return \Doctrine\ORM\EntityManager::create($conn, $config, $evm);