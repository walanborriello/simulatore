<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'students_managements')]
class StudentManagement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $studentId;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $fromToken = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $toToken;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $modifiedAt;

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

    public function getFromToken(): ?string
    {
        return $this->fromToken;
    }

    public function setFromToken(?string $fromToken): self
    {
        $this->fromToken = $fromToken;
        return $this;
    }

    public function getToToken(): string
    {
        return $this->toToken;
    }

    public function setToToken(string $toToken): self
    {
        $this->toToken = $toToken;
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
