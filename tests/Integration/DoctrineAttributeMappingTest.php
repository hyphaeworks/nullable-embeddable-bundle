<?php

declare(strict_types=1);

namespace Hyphaeworks\NullableEmbeddableBundle\Tests\Integration;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\TestCase;
use Hyphaeworks\NullableEmbeddableBundle\Doctrine\NullableEmbeddableSubscriber;
use Hyphaeworks\NullableEmbeddableBundle\Tests\Integration\Entity\AttributesTestEntity;
use Hyphaeworks\NullableEmbeddableBundle\Tests\Integration\Entity\EmailAddress;

class DoctrineAttributeMappingTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private NullableEmbeddableSubscriber $subscriber;

    protected function setUp(): void
    {
        $config = ORMSetup::createAttributeMetadataConfiguration(
            paths: [__DIR__.'/Entity'],
            isDevMode: true,
        );
        if (\PHP_VERSION_ID >= 80400 && method_exists($config, 'enableNativeLazyObjects')) {
            $config->enableNativeLazyObjects(true);
        }
        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ], $config);
        $this->entityManager = new EntityManager($connection, $config);

        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->createSchema($metadata);

        $this->subscriber = new NullableEmbeddableSubscriber();
        $this->entityManager->getEventManager()->addEventSubscriber($this->subscriber);
    }

    public function testEmbeddableBecomesNullWhenEmpty(): void
    {
        $entity = new AttributesTestEntity();
        $embeddable = new EmailAddress(address: null);
        $entity->setEmailAddress($embeddable);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
        $this->entityManager->clear();
        $reloadedEntity = $this->entityManager->find(AttributesTestEntity::class, $entity->getId());

        $this->assertNull($reloadedEntity->getEmailAddress());
    }
}
