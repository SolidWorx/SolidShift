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

use App\Repository\LocationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;
use function array_reverse;
use function implode;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
#[UniqueEntity(fields: ['name', 'site', 'parent'])]
#[ORM\UniqueConstraint(columns: ['name', 'site_id', 'parent_id'])]
class Location implements Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME)]
    private Ulid $id;

    #[ORM\Column(length: 75)]
    #[Assert\NotBlank()]
    #[Assert\Length(min: 3, max: 75)]
    private string $name;

    #[ORM\ManyToOne(inversedBy: 'locations')]
    #[ORM\JoinColumn(nullable: false)]
    private Site $site;

    /**
     * @var Collection<int, Schedule>
     */
    #[ORM\OneToMany(mappedBy: 'location', targetEntity: Schedule::class, orphanRemoval: true)]
    private Collection $schedules;

    public function __construct(
        ?string $name = null,
        #[ORM\ManyToOne(targetEntity: self::class)]
        private ?self $parent = null,
        ?Site $site = null,
    ) {
        $this->name = (string) $name;
        $this->schedules = new ArrayCollection();
        $this->id = new Ulid();

        if ($site instanceof Site) {
            $this->site = $site;
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

    public function setName(?string $name): static
    {
        $this->name = (string) $name;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    /**
     * @return list<Location>
     */
    public function getChildren(bool $includeGrandChildren = true): array
    {
        $children = [];

        foreach ($this->site->getLocations() as $location) {
            if ($location->getParent() !== $this) {
                continue;
            }

            $children[] = $location;

            if ($includeGrandChildren) {
                $children = [...$children, ...$location->getChildren()];
            }
        }

        return $children;
    }

    /**
     * @return array{children: list<mixed>, name: string}
     */
    public function getTree(): array
    {
        $tree = [
            'name' => $this->name,
            'children' => [],
        ];

        foreach ($this->getChildren(includeGrandChildren: false) as $child) {
            $tree['children'][] = $child->getTree();
        }

        return $tree;
    }

    public function setParent(?self $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): static
    {
        if (! $site instanceof Site) {
            unset($this->site);
        } else {
            $this->site = $site;
        }

        return $this;
    }

    /**
     * @return Collection<int, Schedule>
     */
    public function getSchedules(): Collection
    {
        return $this->schedules;
    }

    public function addSchedule(Schedule $schedule): static
    {
        if (! $this->schedules->contains($schedule)) {
            $this->schedules->add($schedule);
            $schedule->setLocation($this);
        }

        return $this;
    }

    public function removeSchedule(Schedule $schedule): static
    {
        // set the owning side to null (unless already changed)
        if ($this->schedules->removeElement($schedule) && $schedule->getLocation() === $this) {
            $schedule->setLocation(null);
        }

        return $this;
    }

    public function __toString(): string
    {
        $names = [];

        $parent = $this->parent;

        while ($parent instanceof self) {
            $names[] = $parent->getName();
            $parent = $parent->getParent();
        }

        array_unshift($names, $this->getName());

        return implode(' > ', array_reverse($names));
    }
}
