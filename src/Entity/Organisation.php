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

use App\Repository\OrganisationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: OrganisationRepository::class)]
class Organisation implements Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME)]
    private Ulid $id;

    #[ORM\Column(length: 255)]
    private string $name;

    /**
     * @var Collection<int, Site>
     */
    #[ORM\OneToMany(mappedBy: 'organisation', targetEntity: Site::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $sites;

    /**
     * @var Collection<int, Role>
     */
    #[ORM\OneToMany(mappedBy: 'organisation', targetEntity: Role::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $roles;

    /**
     * @var Collection<int, ShiftTemplate>
     */
    #[ORM\OneToMany(mappedBy: 'organisation', targetEntity: ShiftTemplate::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $shiftTemplates;

    public function __construct()
    {
        $this->id = new Ulid();
        $this->sites = new ArrayCollection();
        $this->roles = new ArrayCollection();
        $this->shiftTemplates = new ArrayCollection();
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

    /**
     * @return Collection<int, Site>
     */
    public function getSites(): Collection
    {
        return $this->sites;
    }

    public function addSite(Site $site): static
    {
        if (! $this->sites->contains($site)) {
            $this->sites->add($site);
            $site->setOrganisation($this);
        }

        return $this;
    }

    public function removeSite(Site $site): static
    {
        // set the owning side to null (unless already changed)
        if ($this->sites->removeElement($site) && $site->getOrganisation() === $this) {
            $site->setOrganisation(null);
        }

        return $this;
    }

    /**
     * @return Collection<int, Role>
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(Role $role): static
    {
        if (! $this->roles->contains($role)) {
            $this->roles->add($role);
            $role->setOrganisation($this);
        }

        return $this;
    }

    public function removeRole(Role $role): static
    {
        $this->roles->removeElement($role);

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
            $shiftTemplate->setOrganisation($this);
        }

        return $this;
    }

    public function removeShiftTemplate(ShiftTemplate $shiftTemplate): static
    {
        $this->shiftTemplates->removeElement($shiftTemplate);

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
