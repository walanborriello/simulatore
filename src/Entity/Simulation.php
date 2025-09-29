<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'simulations')]
class Simulation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $studentId;

    #[ORM\Column(type: 'string', length: 10)]
    private string $cdl;

    #[ORM\Column(type: 'json')]
    private array $inputData = [];

    #[ORM\Column(type: 'json')]
    private array $detailResults = [];

    #[ORM\Column(type: 'json')]
    private array $summaryResults = [];

    #[ORM\Column(type: 'json')]
    private array $leftoverResults = [];

    #[ORM\Column(type: 'integer')]
    private int $totalCfuRecognized = 0;

    #[ORM\Column(type: 'integer')]
    private int $totalCfuRequired = 0;

    #[ORM\Column(type: 'integer')]
    private int $totalCfuIntegrative = 0;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $createdAt;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $updatedAt;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $managedBy = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
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

    public function getCdl(): string
    {
        return $this->cdl;
    }

    public function setCdl(string $cdl): self
    {
        $this->cdl = $cdl;
        return $this;
    }

    public function getInputData(): array
    {
        return $this->inputData;
    }

    public function setInputData(array $inputData): self
    {
        $this->inputData = $inputData;
        return $this;
    }

    public function getDetailResults(): array
    {
        return $this->detailResults;
    }

    public function setDetailResults(array $detailResults): self
    {
        $this->detailResults = $detailResults;
        return $this;
    }

    public function getSummaryResults(): array
    {
        return $this->summaryResults;
    }

    public function setSummaryResults(array $summaryResults): self
    {
        $this->summaryResults = $summaryResults;
        return $this;
    }

    public function getLeftoverResults(): array
    {
        return $this->leftoverResults;
    }

    public function setLeftoverResults(array $leftoverResults): self
    {
        $this->leftoverResults = $leftoverResults;
        return $this;
    }

    public function getTotalCfuRecognized(): int
    {
        return $this->totalCfuRecognized;
    }

    public function setTotalCfuRecognized(int $totalCfuRecognized): self
    {
        $this->totalCfuRecognized = $totalCfuRecognized;
        return $this;
    }

    public function getTotalCfuRequired(): int
    {
        return $this->totalCfuRequired;
    }

    public function setTotalCfuRequired(int $totalCfuRequired): self
    {
        $this->totalCfuRequired = $totalCfuRequired;
        return $this;
    }

    public function getTotalCfuIntegrative(): int
    {
        return $this->totalCfuIntegrative;
    }

    public function setTotalCfuIntegrative(int $totalCfuIntegrative): self
    {
        $this->totalCfuIntegrative = $totalCfuIntegrative;
        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getManagedBy(): ?string
    {
        return $this->managedBy;
    }

    public function setManagedBy(?string $managedBy): self
    {
        $this->managedBy = $managedBy;
        return $this;
    }
}
