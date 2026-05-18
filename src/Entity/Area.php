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

use App\Repository\AreaRepository;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;
use function array_reverse;
use function implode;

#[ORM\Entity(repositoryClass: AreaRepository::class)]
#[UniqueEntity(fields: ['name', 'site', 'parent'])]
#[ORM\UniqueConstraint(columns: ['name', 'site_id', 'parent_id'])]
class Area implements Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME)]
    private Ulid $id;

    #[ORM\Column(length: 75)]
    #[Assert\NotBlank()]
    #[Assert\Length(min: 3, max: 75)]
    private string $name;

    #[ORM\ManyToOne(inversedBy: 'areas')]
    #[ORM\JoinColumn(nullable: false)]
    private Site $site;

    public function __construct(
        ?string $name = null,
        #[ORM\ManyToOne(targetEntity: self::class)]
        private ?self $parent = null,
        ?Site $site = null,
    ) {
        $this->name = (string) $name;
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
     * @return list<Area>
     */
    public function getChildren(bool $includeGrandChildren = true): array
    {
        $children = [];

        foreach ($this->site->getAreas() as $area) {
            if ($area->getParent() !== $this) {
                continue;
            }

            $children[] = $area;

            if ($includeGrandChildren) {
                $children = [...$children, ...$area->getChildren()];
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
