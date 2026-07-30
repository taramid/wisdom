<?php

namespace App\Contract\RandomWisdom;

use Symfony\Component\Uid\Uuid;

interface PickerInterface
{
    public function getRandomId(): ?Uuid;
}
