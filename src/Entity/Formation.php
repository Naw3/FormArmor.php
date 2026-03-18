<?php

namespace App\Entity;

use App\Repository\FormationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormationRepository::class)]
class Formation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $libelle = null;

    #[ORM\Column(length: 40)]
    private ?string $niveau = null;

    #[ORM\Column(length: 50)]
    private ?string $typeform = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?bool $diplomante = null;

    #[ORM\Column]
    private ?int $duree = null;

    #[ORM\Column]
    private ?float $coutrevient = null;

    #[ORM\OneToMany(mappedBy: 'formation', targetEntity: PlanFormation::class)]
    private Collection $plans;

    public function __construct()
    {
        $this->plans = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getNiveau(): ?string
    {
        return $this->niveau;
    }

    public function setNiveau(string $niveau): static
    {
        $this->niveau = $niveau;

        return $this;
    }

    public function getTypeform(): ?string
    {
        return $this->typeform;
    }

    public function setTypeform(string $typeform): static
    {
        $this->typeform = $typeform;

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

    public function isDiplomante(): ?bool
    {
        return $this->diplomante;
    }

    public function setDiplomante(bool $diplomante): static
    {
        $this->diplomante = $diplomante;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): static
    {
        $this->duree = $duree;

        return $this;
    }

    public function getCoutrevient(): ?float
    {
        return $this->coutrevient;
    }

    public function setCoutrevient(float $coutrevient): static
    {
        $this->coutrevient = $coutrevient;

        return $this;
    }

    public function affichage(): ?string
    {
        return ($this->libelle . "-" . $this->niveau);
    }

    /**
     * @return Collection<int, PlanFormation>
     */
    public function getPlans(): Collection
    {
        return $this->plans;
    }

    public function addPlan(PlanFormation $plan): static
    {
        if (!$this->plans->contains($plan)) {
            $this->plans->add($plan);
            $plan->setFormation($this);
        }

        return $this;
    }

    public function removePlan(PlanFormation $plan): static
    {
        if ($this->plans->removeElement($plan)) {
            // set the owning side to null (unless already changed)
            if ($plan->getFormation() === $this) {
                $plan->setFormation(null);
            }
        }

        return $this;
    }
}
