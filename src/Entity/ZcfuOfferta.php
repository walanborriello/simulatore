<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'zcfu_offerta')]
class ZcfuOfferta
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'OFF_ID', type: 'integer')]
    private ?int $offId = null;

    #[ORM\Column(name: 'ORI_ID', type: 'integer', nullable: true)]
    private ?int $oriId = null;

    #[ORM\Column(name: 'DIS_ID', type: 'integer', nullable: true)]
    private ?int $disId = null;

    #[ORM\Column(name: 'rosa', type: 'integer', nullable: true)]
    private ?int $rosa = null;

    #[ORM\Column(name: 'maxCFU', type: 'integer', nullable: true)]
    private ?int $maxCFU = null;

    #[ORM\Column(name: 'TAF', type: 'string', length: 255, nullable: true)]
    private ?string $taf = null;

    #[ORM\Column(name: 'CFU', type: 'integer', nullable: true)]
    private ?int $cfu = null;

    #[ORM\Column(name: 'ANNO', type: 'integer', nullable: true)]
    private ?int $anno = null;

    #[ORM\Column(name: 'AA', type: 'string', length: 255, nullable: true)]
    private ?string $aa = null;

    #[ORM\Column(name: 'CDL', type: 'string', length: 255, nullable: true)]
    private ?string $cdl = null;

    public function getOffId(): ?int
    {
        return $this->offId;
    }

    public function getOriId(): ?int
    {
        return $this->oriId;
    }

    public function setOriId(?int $oriId): self
    {
        $this->oriId = $oriId;
        return $this;
    }

    public function getDisId(): ?int
    {
        return $this->disId;
    }

    public function setDisId(?int $disId): self
    {
        $this->disId = $disId;
        return $this;
    }

    public function getRosa(): ?int
    {
        return $this->rosa;
    }

    public function setRosa(?int $rosa): self
    {
        $this->rosa = $rosa;
        return $this;
    }

    public function getMaxCFU(): ?int
    {
        return $this->maxCFU;
    }

    public function setMaxCFU(?int $maxCFU): self
    {
        $this->maxCFU = $maxCFU;
        return $this;
    }

    public function getTaf(): ?string
    {
        return $this->taf;
    }

    public function setTaf(?string $taf): self
    {
        $this->taf = $taf;
        return $this;
    }

    public function getCfu(): ?int
    {
        return $this->cfu;
    }

    public function setCfu(?int $cfu): self
    {
        $this->cfu = $cfu;
        return $this;
    }

    public function getAnno(): ?int
    {
        return $this->anno;
    }

    public function setAnno(?int $anno): self
    {
        $this->anno = $anno;
        return $this;
    }

    public function getAa(): ?string
    {
        return $this->aa;
    }

    public function setAa(?string $aa): self
    {
        $this->aa = $aa;
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
