<?php

namespace App\Entity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    private ?string $lastname = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Objectif::class, orphanRemoval: true)]
    private Collection $objectifs;

    public function __construct()
    {
        $this->objectifs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }
 
    # Fonction obligatoire du UserInterface
    public function getRoles(): array
    {
        return $this->roles ?? []; #modifier
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles ?? [];

        return $this;
    }

    public function eraseCredentials(): void
    {
        // Laisse vide sauf si tu stockes des infos sensibles temporairement
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * @return Collection<int, Objectif>
     */
    public function getObjectifs(): Collection
    {
        return $this->objectifs;
    }

    public function addObjectif(Objectif $objectif): static
    {
        if (!$this->objectifs->contains($objectif)) {
            $this->objectifs->add($objectif);
            $objectif->setUser($this);
        }

        return $this;
    }

    public function removeObjectif(Objectif $objectif): static
    {
        if ($this->objectifs->removeElement($objectif)) {
            // set the owning side to null (unless already changed)
            if ($objectif->getUser() === $this) {
                $objectif->setUser(null);
            }
        }

        return $this;
    }
    
    public function hasObjectif(): bool
    {
        return !$this->objectifs->isEmpty();
    }
}