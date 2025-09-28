<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'zcfu_regole')]
class ZcfuRegole
{
    #[ORM\Id]
    #[ORM\Column(name: 'ID_off', type: 'integer')]
    private int $idOff;

    #[ORM\Id]
    #[ORM\Column(name: 'ID_ric', type: 'integer')]
    private int $idRic;

    #[ORM\Column(name: 'priorita', type: 'integer')]
    private int $priorita;

    public function getIdOff(): int
    {
        return $this->idOff;
    }

    public function setIdOff(int $idOff): self
    {
        $this->idOff = $idOff;
        return $this;
    }

    public function getIdRic(): int
    {
        return $this->idRic;
    }

    public function setIdRic(int $idRic): self
    {
        $this->idRic = $idRic;
        return $this;
    }

    public function getPriorita(): int
    {
        return $this->priorita;
    }

    public function setPriorita(int $priorita): self
    {
        $this->priorita = $priorita;
        return $this;
    }
}
