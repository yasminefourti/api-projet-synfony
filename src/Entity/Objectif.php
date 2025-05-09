<?php

namespace App\Entity;
use App\Repository\ObjectifRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: ObjectifRepository::class)]
#[UniqueEntity(
    fields: ['user'],
    message: 'Vous avez déjà créé un objectif. Vous ne pouvez avoir qu\'un seul objectif actif.'
)]
class Objectif
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['goal:list', 'goal:detail', 'objectif:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre est obligatoire")]
    #[Assert\Length(min: 3, max: 255)]
    #[Groups(['goal:list', 'goal:detail', 'objectif:read', 'objectif:write'])]
    private ?string $title = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le montant cible est obligatoire")]
    #[Assert\Positive(message: "Le montant cible doit être positif")]
    #[Groups(['goal:list', 'goal:detail', 'objectif:read', 'objectif:write'])]
    private ?float $targetAmount = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le montant actuel est requis")]
    #[Assert\PositiveOrZero(message: "Le montant actuel doit être positif ou zéro")]
    #[Groups(['goal:list', 'goal:detail', 'objectif:read', 'objectif:write'])]
    private ?float $currentAmount = 0.0;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotBlank(message: "La date de début est obligatoire")]
    #[Groups(['goal:list', 'goal:detail', 'objectif:read', 'objectif:write'])]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotBlank(message: "La date de fin est obligatoire")]
    #[Assert\GreaterThan(propertyPath: "startDate", message: "La date de fin doit être postérieure à la date de début")]
    #[Groups(['goal:list', 'goal:detail', 'objectif:read', 'objectif:write'])]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'objectifs')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['goal:detail'])]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'objectif', targetEntity: Transaction::class, orphanRemoval: true)]
    #[Groups(['goal:detail'])]
    private Collection $transactions;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
        $this->currentAmount = 0.0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getTargetAmount(): ?float
    {
        return $this->targetAmount;
    }

    public function setTargetAmount(float $targetAmount): static
    {
        $this->targetAmount = $targetAmount;

        return $this;
    }

    public function getCurrentAmount(): ?float
    {
        return $this->currentAmount;
    }

    public function setCurrentAmount(float $currentAmount): static
    {
        $this->currentAmount = $currentAmount;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): static
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
            $transaction->setObjectif($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): static
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getObjectif() === $this) {
                $transaction->setObjectif(null);
            }
        }

        return $this;
    }
}