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

use App\Repository\ShiftRequirementRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A staffing slot required by an OccurrenceTemplate: "we need 2 bartenders in
 * the Bar area, 18:00-22:00". Concrete Shifts fulfil a ShiftRequirement.
 *
 * If startTime/endTime are null the requirement inherits the times from its
 * OccurrenceTemplate.
 */
#[ORM\Entity(repositoryClass: ShiftRequirementRepository::class)]
class ShiftRequirement
{
    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME)]
    private Ulid $id;

    #[ORM\ManyToOne(inversedBy: 'requirements')]
    #[ORM\JoinColumn(nullable: false)]
    private OccurrenceTemplate $occurrenceTemplate;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Role is required')]
    private Role $role;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Area $area = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $startTime = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $endTime = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive]
    #[Assert\LessThanOrEqual(propertyPath: 'requiredMax')]
    private ?int $requiredMin = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive]
    #[Assert\GreaterThanOrEqual(propertyPath: 'requiredMin')]
    private ?int $requiredMax = null;

    /**
     * Optional template the requirement was seeded from. Kept for traceability
     * but not used for runtime behaviour.
     */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?ShiftTemplate $template = null;

    public function __construct(
        ?OccurrenceTemplate $occurrenceTemplate = null,
        ?Role $role = null,
        ?Area $area = null,
    ) {
        $this->id = new Ulid();
        $this->area = $area;

        if ($occurrenceTemplate instanceof OccurrenceTemplate) {
            $this->occurrenceTemplate = $occurrenceTemplate;
        }

        if ($role instanceof Role) {
            $this->role = $role;
        }
    }

    public function getId(): Ulid
    {
        return $this->id;
    }

    public function getOccurrenceTemplate(): OccurrenceTemplate
    {
        return $this->occurrenceTemplate;
    }

    public function setOccurrenceTemplate(OccurrenceTemplate $occurrenceTemplate): static
    {
        $this->occurrenceTemplate = $occurrenceTemplate;

        return $this;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function setRole(Role $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getArea(): ?Area
    {
        return $this->area;
    }

    public function setArea(?Area $area): static
    {
        $this->area = $area;

        return $this;
    }

    public function getStartTime(): ?DateTimeImmutable
    {
        return $this->startTime ?? (isset($this->occurrenceTemplate) ? $this->occurrenceTemplate->getStartTime() : null);
    }

    public function setStartTime(?DateTimeImmutable $startTime): static
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?DateTimeImmutable
    {
        return $this->endTime ?? (isset($this->occurrenceTemplate) ? $this->occurrenceTemplate->getEndTime() : null);
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

    public function getTemplate(): ?ShiftTemplate
    {
        return $this->template;
    }

    public function setTemplate(?ShiftTemplate $template): static
    {
        $this->template = $template;

        if ($template instanceof ShiftTemplate) {
            if (! isset($this->role) && $template->getRole() instanceof Role) {
                $this->role = $template->getRole();
            }
            $this->area ??= $template->getArea();
            $this->startTime ??= $template->getStartTime();
            $this->endTime ??= $template->getEndTime();
            $this->requiredMin ??= $template->getRequiredMin();
            $this->requiredMax ??= $template->getRequiredMax();
        }

        return $this;
    }
}
