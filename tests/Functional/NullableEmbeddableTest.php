<?php

declare(strict_types=1);

namespace Hyphaeworks\NullableEmbeddableBundle\Tests\Functional;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity\AttributesTestEntity;
use Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity\ChildEntity;
use Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity\ChildWithOwnEntityListener;
use Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity\ContactCard;
use Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity\CountryCode;
use Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity\EmailAddress;
use Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity\EmailAddressWithUnmappedState;
use Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity\EntityExtendingMappedSuperclass;
use Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity\EntityWithContactCard;
use Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity\EntityWithNestedEmbeddable;
use Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity\EntityWithNotNullableColumnEmbeddable;
use Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity\EntityWithReadonlyEmbedded;
use Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity\EntityWithReadonlyNestedEmbedded;
use Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity\EntityWithStrictEmailAddress;
use Hyphaeworks\NullableEmbeddableBundle\Tests\Functional\Entity\EntityWithUnmappedEmbeddableState;
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
    public function emptyEmbeddableOfXmlMappedEntityWithUntypedNullableEmbeddedPropertyIsLoadedAsNull(): void
    {
        $entity = new XmlTestEntity();
        $entity->setEmailAddress(new EmailAddress(address: null));

        $reloaded = $this->persistAndReload($entity);

        self::assertNull($reloaded->getEmailAddress());
    }

    #[Test]
    public function emptyEmbeddableOfChildEntityIsLoadedAsNull(): void
    {
        $entity = new ChildEntity();
        $entity->setEmailAddress(new EmailAddress(address: null));

        $reloaded = $this->persistAndReload($entity);

        self::assertNull($reloaded->getEmailAddress());
    }

    #[Test]
    public function emptyEmbeddableOfChildWithOwnEntityListenerIsLoadedAsNull(): void
    {
        $entity = new ChildWithOwnEntityListener();
        $entity->setEmailAddress(new EmailAddress(address: null));

        $reloaded = $this->persistAndReload($entity);

        self::assertNull($reloaded->getEmailAddress());
    }

    #[Test]
    public function emptyEmbeddableOfEntityExtendingMappedSuperclassIsLoadedAsNull(): void
    {
        $entity = new EntityExtendingMappedSuperclass();
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
    public function unmappedEmbeddablePropertiesDoNotCountAsValues(): void
    {
        $reloaded = $this->persistAndReload(new EntityWithUnmappedEmbeddableState(new EmailAddressWithUnmappedState()));

        self::assertNull($reloaded->getEmailAddress());
    }

    #[Test]
    public function embeddableHoldingOnlyAnEmptyNonNullableEmbeddableIsLoadedAsNull(): void
    {
        $reloaded = $this->persistAndReload(new EntityWithContactCard(new ContactCard()));

        self::assertNull($reloaded->getContactCard());
    }

    #[Test]
    public function zeroAndEmptyStringAreValues(): void
    {
        $withZero = $this->persistAndReload(new EntityWithNestedEmbeddable(new Price(amount: 0)));
        $entity = new AttributesTestEntity();
        $entity->setEmailAddress(new EmailAddress(address: ''));
        $withEmptyString = $this->persistAndReload($entity);

        self::assertEquals(new Price(amount: 0), $withZero->getPrice());
        self::assertEquals(new EmailAddress(address: ''), $withEmptyString->getEmailAddress());
    }

    #[Test]
    public function refreshedEntityWhoseColumnsWereClearedHasNullEmbeddable(): void
    {
        $entity = new EntityWithNestedEmbeddable(new Price(amount: 100));
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
        $this->entityManager
            ->createQuery('UPDATE '.EntityWithNestedEmbeddable::class.' entity SET entity.price.amount = NULL')
            ->execute();

        $this->entityManager->refresh($entity);

        self::assertNull($entity->getPrice());
    }

    #[Test]
    public function emptyEmbeddableOfIteratedQueryResultIsLoadedAsNull(): void
    {
        $entity = new AttributesTestEntity();
        $entity->setEmailAddress(new EmailAddress(address: null));
        $this->persistAndReload($entity);
        $this->entityManager->clear();

        $query = $this->entityManager->createQuery('SELECT entity FROM '.AttributesTestEntity::class.' entity');
        $loaded = [...$query->toIterable()];

        self::assertCount(1, $loaded);
        self::assertNull($loaded[0]->getEmailAddress());
    }

    #[Test]
    public function valueAssignedToNulledEmbeddableIsSaved(): void
    {
        $entity = new AttributesTestEntity();
        $entity->setEmailAddress(new EmailAddress(address: null));
        $reloaded = $this->persistAndReload($entity);

        $reloaded->setEmailAddress(new EmailAddress(address: 'someone@example.com'));

        self::assertEquals(new EmailAddress(address: 'someone@example.com'), $this->persistAndReload($reloaded)->getEmailAddress());
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
    public function emptyEmbeddableOfReadonlyPropertyIsLeftAsLoaded(): void
    {
        $reloaded = $this->persistAndReload(new EntityWithReadonlyEmbedded(new EmailAddress(address: null)));

        self::assertEquals(new EmailAddress(address: null), $reloaded->emailAddress);
    }

    #[Test]
    public function readonlyNestedEmbeddedPropertyDoesNotStopItsOwnerFromBeingNulled(): void
    {
        $reloaded = $this->persistAndReload(new EntityWithReadonlyNestedEmbedded(new PriceWithReadonlyContact()));

        self::assertNull($reloaded->getPrice());
    }

    #[Test]
    public function embeddableWithNotNullableColumnIsLeftAsLoaded(): void
    {
        $reloaded = $this->persistAndReload(new EntityWithNotNullableColumnEmbeddable(new CountryCode('AT')));

        self::assertEquals(new CountryCode('AT'), $reloaded->getCountryCode());
    }

    #[\Override]
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
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
