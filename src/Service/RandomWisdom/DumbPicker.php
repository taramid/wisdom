<?php

namespace App\Service\RandomWisdom;

use App\Contract\RandomWisdom\PickerInterface;
use App\Repository\WisdomRepository;
use Symfony\Component\Uid\Uuid;

readonly class DumbPicker implements PickerInterface
{
    public function __construct(
        private WisdomRepository $wisdomRepository,
    ) {
    }

    public function getRandomId(): ?Uuid
    {
        // here we do:
        // 1. get count
        // 2. get random UUID
        // 3. (in Repository) get the entity
        //
        // if we move this logic into Repository it will save us 1 query
        //
        // but for architecture sake let it be this way

        $count = $this->wisdomRepository->count();
        if ($count === 0) {
            return null;
        }

        $id = $this->wisdomRepository->createQueryBuilder('w')
            ->select('w.id')
            ->setFirstResult(random_int(0, $count - 1))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $id !== null ? Uuid::fromString($id['id']) : null;
    }
}
