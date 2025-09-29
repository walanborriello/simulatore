<?php

namespace App\Entity;

use App\Validator\CodiceFiscale as CodiceFiscaleConstraint;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'students_prospective')]
class StudentProspective
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank]
    private string $firstName;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank]
    private string $lastName;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private string $email;

    #[ORM\Column(type: 'string', length: 16, unique: true)]
    #[Assert\NotBlank]
    #[CodiceFiscaleConstraint]
    private string $codiceFiscale;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank]
    private string $ateneoProvenienza;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank]
    private string $corsoStudioInteresse;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

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

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getCodiceFiscale(): string
    {
        return $this->codiceFiscale;
    }

    public function setCodiceFiscale(string $codiceFiscale): self
    {
        $this->codiceFiscale = $codiceFiscale;
        return $this;
    }

    public function getAteneoProvenienza(): string
    {
        return $this->ateneoProvenienza;
    }

    public function setAteneoProvenienza(string $ateneoProvenienza): self
    {
        $this->ateneoProvenienza = $ateneoProvenienza;
        return $this;
    }

    public function getCorsoStudioInteresse(): string
    {
        return $this->corsoStudioInteresse;
    }

    public function setCorsoStudioInteresse(string $corsoStudioInteresse): self
    {
        $this->corsoStudioInteresse = $corsoStudioInteresse;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
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

    public function getSimulations(): Collection
    {
        return $this->simulations;
    }

    public function addSimulation(Simulation $simulation): self
    {
        if (!$this->simulations->contains($simulation)) {
            $this->simulations[] = $simulation;
            $simulation->setStudent($this);
        }
        return $this;
    }

    public function removeSimulation(Simulation $simulation): self
    {
        if ($this->simulations->removeElement($simulation)) {
            if ($simulation->getStudent() === $this) {
                $simulation->setStudent(null);
            }
        }
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

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }
}
