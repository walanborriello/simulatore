<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'students_history')]
class StudentManagement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $studentId;

    #[ORM\Column(type: 'string', length: 255)]
    private string $currentToken;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $toToken = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $modifiedAt;

    #[ORM\Column(type: 'string', length: 50)]
    private string $action;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $simulationId = null;

    public function __construct()
    {
        $this->modifiedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStudentId(): int
    {
        return $this->studentId;
    }

    public function setStudentId(int $studentId): self
    {
        $this->studentId = $studentId;
        return $this;
    }

    public function getCurrentToken(): string
    {
        return $this->currentToken;
    }

    public function setCurrentToken(string $currentToken): self
    {
        $this->currentToken = $currentToken;
        return $this;
    }

    public function getToToken(): ?string
    {
        return $this->toToken;
    }

    public function setToToken(?string $toToken): self
    {
        $this->toToken = $toToken;
        return $this;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;
        return $this;
    }

    public function getSimulationId(): ?int
    {
        return $this->simulationId;
    }

    public function setSimulationId(?int $simulationId): self
    {
        $this->simulationId = $simulationId;
        return $this;
    }

    public function getModifiedAt(): \DateTime
    {
        return $this->modifiedAt;
    }

    public function setModifiedAt(\DateTime $modifiedAt): self
    {
        $this->modifiedAt = $modifiedAt;
        return $this;
    }
}
