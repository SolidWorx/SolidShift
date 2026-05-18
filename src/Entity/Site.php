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
    final public const string TABLE_NAME = '`sites`';

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
     * @var Collection<int, Area>
     */
    #[ORM\OneToMany(mappedBy: 'site', targetEntity: Area::class, orphanRemoval: true)]
    #[ORM\OrderBy(['name' => Criteria::ASC])]
    private Collection $areas;

    /**
     * @var Collection<int, UserInvite>
     */
    #[ORM\OneToMany(mappedBy: 'site', targetEntity: UserInvite::class, orphanRemoval: true)]
    private Collection $invites;

    /**
     * @var Collection<int, Schedule>
     */
    #[ORM\OneToMany(mappedBy: 'site', targetEntity: Schedule::class, orphanRemoval: true)]
    private Collection $schedules;

    #[ORM\ManyToOne(inversedBy: 'sites')]
    #[ORM\JoinColumn(nullable: false)]
    private Organisation $organisation;

    #[ORM\Column(length: 32, unique: true, nullable: true)]
    private ?string $selfRegistrationToken = null;

    public function __construct(?string $name = null)
    {
        $this->setName($name);

        $this->id = new Ulid();
        $this->userAccess = new ArrayCollection();
        $this->areas = new ArrayCollection();
        $this->invites = new ArrayCollection();
        $this->schedules = new ArrayCollection();
    }

    public function getId(): Ulid
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = (string) $name;

        $this->slug = new AsciiSlugger()->slug($this->name)->lower()->toString();

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
     * @return Collection<int, Area>
     */
    public function getAreas(): Collection
    {
        return $this->areas;
    }

    public function addArea(Area $area): static
    {
        if (! $this->areas->contains($area)) {
            $this->areas->add($area);
            $area->setSite($this);
        }

        return $this;
    }

    public function removeArea(Area $area): static
    {
        // set the owning side to null (unless already changed)
        if ($this->areas->removeElement($area) && $area->getSite() === $this) {
            $area->setSite(null);
        }

        return $this;
    }

    /**
     * @return Collection<int, UserInvite>
     */
    public function getInvites(): Collection
    {
        return $this->invites;
    }

    public function addInvite(UserInvite $invite): static
    {
        if (! $this->invites->contains($invite)) {
            $this->invites->add($invite);
            $invite->setSite($this);
        }

        return $this;
    }

    public function removeInvite(UserInvite $invite): static
    {
        // set the owning side to null (unless already changed)
        if ($this->invites->removeElement($invite) && $invite->getSite() === $this) {
            $invite->setSite(null);
        }

        return $this;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

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
            $schedule->setSite($this);
        }

        return $this;
    }

    public function removeSchedule(Schedule $schedule): static
    {
        // set the owning side to null (unless already changed)
        if ($this->schedules->removeElement($schedule) && $schedule->getSite() === $this) {
            $schedule->setSite(null);
        }

        return $this;
    }

    public function getOrganisation(): ?Organisation
    {
        return $this->organisation;
    }

    public function setOrganisation(?Organisation $organisation): static
    {
        if (! $organisation instanceof Organisation) {
            unset($this->organisation);
        } else {
            $this->organisation = $organisation;
        }

        return $this;
    }

    public function getSelfRegistrationToken(): ?string
    {
        return $this->selfRegistrationToken;
    }

    public function setSelfRegistrationToken(?string $token): static
    {
        $this->selfRegistrationToken = $token;

        return $this;
    }
}
