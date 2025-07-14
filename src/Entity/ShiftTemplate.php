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

use App\Repository\ShiftTemplateRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ShiftTemplateRepository::class)]
class ShiftTemplate implements Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME)]
    private Ulid $id;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'shiftTemplates')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Position is required')]
    private ?Position $position = null;

    #[ORM\ManyToOne(inversedBy: 'shiftTemplates')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Location is required')]
    private ?Location $location = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $startTime = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $endTime = null;

    #[ORM\Column(nullable: true)]
    #[Assert\LessThanOrEqual(propertyPath: 'requiredMax')]
    #[Assert\Positive]
    private ?int $requiredMin = null;

    #[ORM\Column(nullable: true)]
    #[Assert\GreaterThanOrEqual(propertyPath: 'requiredMin')]
    #[Assert\Positive]
    private ?int $requiredMax = null;

    public function __construct()
    {
        $this->id = new Ulid();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPosition(): ?Position
    {
        return $this->position;
    }

    public function setPosition(?Position $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getStartTime(): ?DateTimeImmutable
    {
        return $this->startTime;
    }

    public function setStartTime(?DateTimeImmutable $startTime): static
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?DateTimeImmutable
    {
        return $this->endTime;
    }

    public function setEndTime(?DateTimeImmutable $endTime): static
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getRequiredMin(): ?int
    {
        return $this->requiredMin;
    }

    public function setRequiredMin(?int $requiredMin): static
    {
        $this->requiredMin = $requiredMin;

        return $this;
    }

    public function getRequiredMax(): ?int
    {
        return $this->requiredMax;
    }

    public function setRequiredMax(?int $requiredMax): static
    {
        $this->requiredMax = $requiredMax;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
