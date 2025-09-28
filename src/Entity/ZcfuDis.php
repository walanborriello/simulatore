<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'zcfu_dis')]
class ZcfuDis
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'DIS_ID', type: 'integer')]
    private ?int $disId = null;

    #[ORM\Column(name: 'disciplina', type: 'string', length: 255, nullable: true)]
    private ?string $disciplina = null;

    #[ORM\Column(name: 'ssd', type: 'string', length: 255, nullable: true)]
    private ?string $ssd = null;

    public function getDisId(): ?int
    {
        return $this->disId;
    }

    public function getDisciplina(): ?string
    {
        return $this->disciplina;
    }

    public function setDisciplina(?string $disciplina): self
    {
        $this->disciplina = $disciplina;
        return $this;
    }

    public function getSsd(): ?string
    {
        return $this->ssd;
    }

    public function setSsd(?string $ssd): self
    {
        $this->ssd = $ssd;
        return $this;
    }
}
