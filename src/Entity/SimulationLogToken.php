<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'simulation_log_token')]
class SimulationLogToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Simulation::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Simulation $simulation;

    #[ORM\Column(type: 'string', length: 255)]
    private string $oldToken;

    #[ORM\Column(type: 'string', length: 255)]
    private string $newToken;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $changedAt;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $changedBy = null;

    public function __construct()
    {
        $this->changedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSimulation(): Simulation
    {
        return $this->simulation;
    }

    public function setSimulation(Simulation $simulation): self
    {
        $this->simulation = $simulation;
        return $this;
    }

    public function getOldToken(): string
    {
        return $this->oldToken;
    }

    public function setOldToken(string $oldToken): self
    {
        $this->oldToken = $oldToken;
        return $this;
    }

    public function getNewToken(): string
    {
        return $this->newToken;
    }

    public function setNewToken(string $newToken): self
    {
        $this->newToken = $newToken;
        return $this;
    }

    public function getChangedAt(): \DateTime
    {
        return $this->changedAt;
    }

    public function setChangedAt(\DateTime $changedAt): self
    {
        $this->changedAt = $changedAt;
        return $this;
    }

    public function getChangedBy(): ?string
    {
        return $this->changedBy;
    }

    public function setChangedBy(?string $changedBy): self
    {
        $this->changedBy = $changedBy;
        return $this;
    }
}
