<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Entity;

use App\Repository\PositionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PositionRepository::class)]
class Position implements Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank()]
    #[Assert\Length(min: 3, max: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, ShiftTemplate>
     */
    #[ORM\OneToMany(targetEntity: ShiftTemplate::class, mappedBy: 'position')]
    private Collection $shiftTemplates;

    public function __construct(?string $name = null)
    {
        $this->name = $name;
        $this->shiftTemplates = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, ShiftTemplate>
     */
    public function getShiftTemplates(): Collection
    {
        return $this->shiftTemplates;
    }

    public function addShiftTemplate(ShiftTemplate $shiftTemplate): static
    {
        if (! $this->shiftTemplates->contains($shiftTemplate)) {
            $this->shiftTemplates->add($shiftTemplate);
            $shiftTemplate->setPosition($this);
        }

        return $this;
    }

    public function removeShiftTemplate(ShiftTemplate $shiftTemplate): static
    {
        // set the owning side to null (unless already changed)
        if ($this->shiftTemplates->removeElement($shiftTemplate) && $shiftTemplate->getPosition() === $this) {
            $shiftTemplate->setPosition(null);
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
