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

use App\Repository\SiteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @codeCoverageIgnore
 */
#[ORM\Entity(repositoryClass: SiteRepository::class)]
#[ORM\Table(name: Site::TABLE_NAME)]
class Site implements Stringable
{
    final public const TABLE_NAME = '`sites`';

    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME)]
    private Ulid $id;

    #[ORM\Column(length: 45)]
    #[Assert\NotBlank(message: 'Please enter a name for the site')]
    #[Assert\Length(min: 3, max: 45, minMessage: 'The site name must be at least {{ limit }} characters long', maxMessage: 'The site name must be less than {{ limit }} characters long')]
    private string $name = '';

    #[ORM\Column(length: 75)]
    private string $slug = '';

    /**
     * @var Collection<int, UserSiteAccess>
     */
    #[ORM\OneToMany(mappedBy: 'site', targetEntity: UserSiteAccess::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $userAccess;

    /**
     * @var Collection<int, Location>
     */
    #[ORM\OneToMany(mappedBy: 'site', targetEntity: Location::class, orphanRemoval: true)]
    #[ORM\OrderBy(['name' => Criteria::ASC])]
    private Collection $locations;

    public function __construct(?string $name = null)
    {
        $this->setName($name);

        $this->id = new Ulid();
        $this->userAccess = new ArrayCollection();
        $this->locations = new ArrayCollection();
    }

    public function getId(): Ulid
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = (string) $name;

        $this->slug = (new AsciiSlugger())->slug($this->name)->lower()->toString();

        return $this;
    }

    /**
     * @return Collection<int, UserSiteAccess>
     */
    public function getUserAccess(): Collection
    {
        return $this->userAccess;
    }

    public function addUserAccess(UserSiteAccess $userAccess): static
    {
        if (! $this->userAccess->contains($userAccess)) {
            $this->userAccess->add($userAccess);
            $userAccess->setSite($this);
        }

        return $this;
    }

    public function removeUserAccess(UserSiteAccess $userAccess): static
    {
        // set the owning side to null (unless already changed)
        if ($this->userAccess->removeElement($userAccess) && $userAccess->getSite() === $this) {
            $userAccess->setSite(null);
        }

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    /**
     * @return Collection<int, Location>
     */
    public function getLocations(): Collection
    {
        return $this->locations;
    }

    public function addLocation(Location $location): static
    {
        if (! $this->locations->contains($location)) {
            $this->locations->add($location);
            $location->setSite($this);
        }

        return $this;
    }

    public function removeLocation(Location $location): static
    {
        // set the owning side to null (unless already changed)
        if ($this->locations->removeElement($location) && $location->getSite() === $this) {
            $location->setSite(null);
        }

        return $this;
    }
}
