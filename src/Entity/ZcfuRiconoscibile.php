<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'zcfu_riconoscibile')]
class ZcfuRiconoscibile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'ID_ric', type: 'integer')]
    private ?int $idRic = null;

    #[ORM\Column(name: 'riconoscibile', type: 'string', length: 255, nullable: true)]
    private ?string $riconoscibile = null;

    #[ORM\Column(name: 'CDL', type: 'string', length: 255, nullable: true)]
    private ?string $cdl = null;

    public function getIdRic(): ?int
    {
        return $this->idRic;
    }

    public function getRiconoscibile(): ?string
    {
        return $this->riconoscibile;
    }

    public function setRiconoscibile(?string $riconoscibile): self
    {
        $this->riconoscibile = $riconoscibile;
        return $this;
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
}
