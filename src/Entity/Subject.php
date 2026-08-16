<?php

namespace App\Entity;

use App\Repository\SubjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: SubjectRepository::class)]
class Subject
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    /**
     * @var Collection<int, Wisdom>
     */
    #[ORM\OneToMany(targetEntity: Wisdom::class, mappedBy: 'subject', cascade: ['persist'])]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $wisdoms;

    public function __construct()
    {
        $this->wisdoms = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id->toBase58();
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Collection<int, Wisdom>
     */
    public function getWisdoms(): Collection
    {
        return $this->wisdoms;
    }

    public function addWisdom(Wisdom $wisdom): static
    {
        if (!$this->wisdoms->contains($wisdom)) {
            $this->wisdoms->add($wisdom);
            $wisdom->setSubject($this);
        }

        return $this;
    }

    public function removeWisdom(Wisdom $wisdom): static
    {
        if ($this->wisdoms->removeElement($wisdom)) {
            // set the owning side to null (unless already changed)
            if ($wisdom->getSubject() === $this) {
                $wisdom->setSubject(null);
            }
        }

        return $this;
    }
}
