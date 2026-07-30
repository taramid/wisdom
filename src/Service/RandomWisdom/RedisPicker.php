<?php

namespace App\Service\RandomWisdom;

use App\Contract\RandomWisdom\PickerInterface;
use App\Contract\RandomWisdom\StorageInterface;
use App\Repository\WisdomRepository;
use Symfony\Component\Uid\Uuid;

readonly class RedisPicker implements PickerInterface, StorageInterface
{
    private const string KEY = 'all_wisdom_ids';
    private const int TTL = 3600;

    public function __construct(
        private WisdomRepository $wisdomRepository,
        private \Redis $redis,
    ) {
    }

    public function getRandomId(): ?Uuid
    {
        $id = $this->redis->sRandMember(self::KEY);

        if ($id === false) {
            $this->populate();
            $id = $this->redis->sRandMember(self::KEY);
        }

        return $id !== false ? Uuid::fromBase58($id) : null;
    }

    private function populate(): void
    {
        $ids = $this->wisdomRepository->getAllIds();

        if (!empty($ids)) {

            $this->redis->sAdd(self::KEY, ...array_map(fn ($id) => new Uuid($id)->toBase58(), $ids));
            $this->redis->expire(self::KEY, self::TTL);
        }
    }

    public function add(string $id): void
    {
        if ($this->redis->exists(self::KEY)) {
            $this->redis->sAdd(self::KEY, Uuid::fromString($id)->toBase58());
        }
    }

    public function remove(string $id): void
    {
        dump(__METHOD__);
        dump($id);
        if ($this->redis->exists(self::KEY)) {
            $this->redis->sRem(self::KEY, Uuid::fromString($id)->toBase58());
        }
    }

    public function flush(): void
    {
        $this->redis->del(self::KEY);
    }
}
