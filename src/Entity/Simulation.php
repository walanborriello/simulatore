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

    #[ORM\ManyToOne(targetEntity: StudentProspective::class, inversedBy: 'simulations')]
    #[ORM\JoinColumn(nullable: false)]
    private StudentProspective $student;

    #[ORM\Column(type: 'string', length: 50)]
    private string $cdl;

    #[ORM\Column(type: 'json')]
    private array $inputData = [];

    #[ORM\Column(type: 'json')]
    private array $resultData = [];

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $userToken = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $createdAt;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStudent(): StudentProspective
    {
        return $this->student;
    }

    public function setStudent(StudentProspective $student): self
    {
        $this->student = $student;
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

    public function getResultData(): array
    {
        return $this->resultData;
    }

    public function setResultData(array $resultData): self
    {
        $this->resultData = $resultData;
        return $this;
    }

    public function getUserToken(): ?string
    {
        return $this->userToken;
    }

    public function setUserToken(?string $userToken): self
    {
        $this->userToken = $userToken;
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
}
