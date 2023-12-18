<?php

namespace App\Entity;

use App\Repository\SiteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SiteRepository::class)]
#[ORM\Table(name: Site::TABLE_NAME)]
class Site
{
    public const TABLE_NAME = '`sites`';

    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME)]
    private Ulid $id;

    #[ORM\Column(length: 45)]
    #[Assert\NotBlank()]
    #[Assert\Length(min: 3, max: 45)]
    private string $name = '';

    /** @var Collection<int, UserSiteAccess>  */
    #[ORM\OneToMany(mappedBy: 'site', targetEntity: UserSiteAccess::class, orphanRemoval: true)]
    private Collection $userAccess;

    public function __construct()
    {
        $this->id = new Ulid();
        $this->userAccess = new ArrayCollection();
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
     * @return Collection<int, UserSiteAccess>
     */
    public function getUserAccess(): Collection
    {
        return $this->userAccess;
    }

    public function addUserAccess(UserSiteAccess $userAccess): static
    {
        if (!$this->userAccess->contains($userAccess)) {
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
}
