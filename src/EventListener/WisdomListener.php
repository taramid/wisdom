<?php

namespace App\EventListener;

use App\Contract\RandomWisdom\StorageInterface;
use App\Entity\Subject;
use App\Entity\Wisdom;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::postPersist, method: 'onWisdomAdd', entity: Wisdom::class)]
#[AsEntityListener(event: Events::preRemove, method: 'onWisdomRemove', entity: Wisdom::class)]
#[AsEntityListener(event: Events::preRemove, method: 'onSubjectRemove', entity: Subject::class)]
final readonly class WisdomListener
{
    public function __construct(
        private StorageInterface $storage
    ) {}

    public function onWisdomAdd(Wisdom $wisdom): void
    {
        $this->storage->add($wisdom->getId());
    }

    public function onWisdomRemove(Wisdom $wisdom): void
    {
        $this->storage->remove($wisdom->getId());
    }

    public function onSubjectRemove(): void
    {
        $this->storage->flush();
    }
}

//https://symfony.com/doc/current/doctrine/events.html
