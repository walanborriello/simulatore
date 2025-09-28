<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'zcfu_CDL')]
class ZcfuCdl
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'ID', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'CDL', type: 'string', length: 255, nullable: true)]
    private ?string $cdl = null;

    #[ORM\Column(name: 'ID_ORI', type: 'integer', nullable: true)]
    private ?int $idOri = null;

    #[ORM\Column(name: 'Orient', type: 'string', length: 255, nullable: true)]
    private ?string $orient = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCdl(): ?string
    {
        return $this->cdl;
    }

    public function setCdl(?string $cdl): self
    {
        $this->cdl = $cdl;
        return $this;
    }

    public function getIdOri(): ?int
    {
        return $this->idOri;
    }

    public function setIdOri(?int $idOri): self
    {
        $this->idOri = $idOri;
        return $this;
    }

    public function getOrient(): ?string
    {
        return $this->orient;
    }

    public function setOrient(?string $orient): self
    {
        $this->orient = $orient;
        return $this;
    }
}
