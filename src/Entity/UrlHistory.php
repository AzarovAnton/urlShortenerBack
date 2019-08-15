<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UrlHistoryRepository")
 */
class UrlHistory
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $short_url;

    /**
     * @ORM\Column(type="datetime")
     */
    private $usage_date;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $usage_ip;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getShortUrl(): ?string
    {
        return $this->short_url;
    }

    public function setShortUrl(string $short_url): self
    {
        $this->short_url = $short_url;

        return $this;
    }

    public function getUsageDate(): ?\DateTimeInterface
    {
        return $this->usage_date;
    }

    public function setUsageDate(\DateTimeInterface $usage_date): self
    {
        $this->usage_date = $usage_date;

        return $this;
    }

    public function getUsageIp(): ?string
    {
        return $this->usage_ip;
    }

    public function setUsageIp(string $usage_ip): self
    {
        $this->usage_ip = $usage_ip;

        return $this;
    }
}
