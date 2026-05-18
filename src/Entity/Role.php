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

use App\Repository\RoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
class Role implements Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME)]
    private Ulid $id;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank()]
    #[Assert\Length(min: 3, max: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'roles')]
    #[ORM\JoinColumn(nullable: false)]
    private Organisation $organisation;

    /**
     * @var Collection<int, Area>
     */
    #[ORM\ManyToMany(targetEntity: Area::class)]
    #[ORM\JoinTable(name: 'role_allowed_area')]
    private Collection $allowedAreas;

    public function __construct(?string $name = null, ?Organisation $organisation = null)
    {
        $this->id = new Ulid();
        $this->name = $name;
        $this->allowedAreas = new ArrayCollection();

        if ($organisation instanceof Organisation) {
            $this->organisation = $organisation;
        }
    }

    public function getId(): Ulid
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

    public function getOrganisation(): Organisation
    {
        return $this->organisation;
    }

    public function setOrganisation(Organisation $organisation): static
    {
        $this->organisation = $organisation;

        return $this;
    }

    /**
     * @return Collection<int, Area>
     */
    public function getAllowedAreas(): Collection
    {
        return $this->allowedAreas;
    }

    public function addAllowedArea(Area $area): static
    {
        if (! $this->allowedAreas->contains($area)) {
            $this->allowedAreas->add($area);
        }

        return $this;
    }

    public function removeAllowedArea(Area $area): static
    {
        $this->allowedAreas->removeElement($area);

        return $this;
    }

    public function isAllowedInArea(Area $area): bool
    {
        if ($this->allowedAreas->isEmpty()) {
            return true;
        }

        return $this->allowedAreas->contains($area);
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
