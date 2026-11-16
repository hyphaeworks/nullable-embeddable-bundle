<?php

declare(strict_types=1);

namespace Hyphaeworks\NullableEmbeddableBundle\Tests\Functional;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Hyphaeworks\NullableEmbeddableBundle\Exception\ReadonlyEmbeddedPropertyException;
use Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity\AttributesTestEntity;
use Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity\EmailAddress;
use Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity\EntityWithNestedEmbeddable;
use Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity\EntityWithReadonlyEmbedded;
use Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity\EntityWithReadonlyNestedEmbedded;
use Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity\EntityWithStrictEmailAddress;
use Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity\Owner;
use Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity\Price;
use Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity\PriceWithReadonlyContact;
use Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\XmlEntity\XmlTestEntity;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class NullableEmbeddableTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    #[\Override]
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    #[\Override]
    protected function setUp(): void
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        $this->entityManager = $entityManager;
        (new SchemaTool($entityManager))->createSchema($entityManager->getMetadataFactory()->getAllMetadata());
    }

    #[Test]
    public function emptyEmbeddableIsLoadedAsNull(): void
    {
        $entity = new AttributesTestEntity();
        $entity->setEmailAddress(new EmailAddress(address: null));

        $reloaded = $this->persistAndReload($entity);

        self::assertNull($reloaded->getEmailAddress());
    }

    #[Test]
    public function embeddableWithValueIsKept(): void
    {
        $entity = new AttributesTestEntity();
        $entity->setEmailAddress(new EmailAddress(address: 'someone@example.com'));

        $reloaded = $this->persistAndReload($entity);

        self::assertEquals(new EmailAddress(address: 'someone@example.com'), $reloaded->getEmailAddress());
    }

    #[Test]
    public function emptyEmbeddableOfXmlMappedEntityWithUntypedMarkedPropertyIsLoadedAsNull(): void
    {
        $entity = new XmlTestEntity();
        $entity->setEmailAddress(new EmailAddress(address: null));

        $reloaded = $this->persistAndReload($entity);

        self::assertNull($reloaded->getEmailAddress());
    }

    #[Test]
    public function embeddableWithUninitializedPropertiesIsLoadedAsNull(): void
    {
        $reloaded = $this->persistAndReload(new EntityWithStrictEmailAddress());

        self::assertNull($reloaded->getEmailAddress());
    }

    #[Test]
    public function emptyEmbeddableOfLazilyLoadedAssociationIsLoadedAsNull(): void
    {
        $owned = new AttributesTestEntity();
        $owned->setEmailAddress(new EmailAddress(address: null));

        $reloaded = $this->persistAndReload(new Owner($owned));

        self::assertNull($reloaded->getOwned()->getEmailAddress());
    }

    #[Test]
    public function emptyNestedEmbeddableIsLoadedAsNull(): void
    {
        $reloaded = $this->persistAndReload(new EntityWithNestedEmbeddable());

        self::assertNull($reloaded->getPrice());
    }

    #[Test]
    public function emptyNestedEmbeddableIsLoadedAsNullInsideEmbeddableWithValue(): void
    {
        $reloaded = $this->persistAndReload(new EntityWithNestedEmbeddable(new Price(amount: 100)));

        self::assertEquals(new Price(amount: 100), $reloaded->getPrice());
    }

    #[Test]
    public function nulledEmbeddableLeavesNothingToUpdate(): void
    {
        $reloaded = $this->persistAndReload(new EntityWithNestedEmbeddable(new Price()));
        $unitOfWork = $this->entityManager->getUnitOfWork();

        $unitOfWork->computeChangeSets();

        self::assertNull($reloaded->getPrice());
        self::assertSame([], $unitOfWork->getEntityChangeSet($reloaded));
        self::assertFalse($unitOfWork->isScheduledForUpdate($reloaded));
    }

    #[Test]
    public function readonlyEmbeddedPropertyIsRejected(): void
    {
        $this->expectException(ReadonlyEmbeddedPropertyException::class);
        $this->expectExceptionMessage(EntityWithReadonlyEmbedded::class.'::$emailAddress is readonly');

        $this->persistAndReload(new EntityWithReadonlyEmbedded());
    }

    #[Test]
    public function readonlyEmbeddedPropertyIsRejectedEvenWhenEmbeddableHasValue(): void
    {
        $this->expectException(ReadonlyEmbeddedPropertyException::class);

        $this->persistAndReload(new EntityWithReadonlyEmbedded(new EmailAddress(address: 'someone@example.com')));
    }

    #[Test]
    public function readonlyNestedEmbeddedPropertyIsRejected(): void
    {
        $this->expectException(ReadonlyEmbeddedPropertyException::class);
        $this->expectExceptionMessage(PriceWithReadonlyContact::class.'::$contact is readonly');

        $this->persistAndReload(new EntityWithReadonlyNestedEmbedded(new PriceWithReadonlyContact(amount: 100)));
    }

    /**
     * @template T of object
     *
     * @param T $entity
     *
     * @return T
     */
    private function persistAndReload(object $entity): object
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
        $identifier = $this->entityManager->getUnitOfWork()->getSingleIdentifierValue($entity);
        $this->entityManager->clear();

        $reloaded = $this->entityManager->find($entity::class, $identifier);
        self::assertNotNull($reloaded);

        return $reloaded;
    }
}
