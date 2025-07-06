<?php

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

    public function __construct()
    {
        $this->id = new Ulid();
        $this->sites = new ArrayCollection();
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
        if (!$this->sites->contains($site)) {
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

    public function __toString(): string
    {
        return $this->name;
    }
}
