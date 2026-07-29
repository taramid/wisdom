<?php

namespace App\Twig\Components;

use App\Entity\Subject as SubjectEntity;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Subject
{
    public SubjectEntity $subject;
}
