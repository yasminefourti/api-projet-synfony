<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['transaction:read', 'goal:detail'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le type de transaction est obligatoire")]
    #[Assert\Choice(choices: ["dépense", "recette"], message: "Le type doit être 'dépense' ou 'recette'")]
    #[Groups(['transaction:read', 'transaction:write', 'goal:detail'])]
    private ?string $type = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le montant est obligatoire")]
    #[Assert\Positive(message: "Le montant doit être positif")]
    #[Groups(['transaction:read', 'transaction:write', 'goal:detail'])]
    private ?float $amount = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotBlank(message: "La date est obligatoire")]
    #[Groups(['transaction:read', 'transaction:write', 'goal:detail'])]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(targetEntity: Objectif::class, inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Objectif $objectif = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['transaction:read', 'transaction:write', 'goal:detail'])]
    private ?string $description = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getObjectif(): ?Objectif
    {
        return $this->objectif;
    }

    public function setObjectif(?Objectif $objectif): static
    {
        $this->objectif = $objectif;

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
}