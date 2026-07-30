<?php

namespace App\Contract\RandomWisdom;

use Symfony\Component\Uid\Uuid;

interface StorageInterface
{
    public function add(string $id): void;
    public function remove(string $id): void;
    public function flush(): void;
}
