<?php

namespace App\Service\RandomWisdom;

use App\Contract\RandomWisdom\StorageInterface;

readonly class NullStorage implements StorageInterface
{
    public function add(string $id): void
    {
    }

    public function remove(string $id): void
    {
    }

    public function flush(): void
    {
    }
}
