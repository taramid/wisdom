<?php

namespace App\Twig\Components;

use App\Entity\Wisdom as WisdomEntity;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Wisdom
{
    public WisdomEntity $wisdom;
}
