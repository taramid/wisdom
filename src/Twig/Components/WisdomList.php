<?php

namespace App\Twig\Components;

use App\Entity\Wisdom as WisdomEntity;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class WisdomList
{
    /** @var WisdomEntity[] */
    public array $wisdoms = [];
}
