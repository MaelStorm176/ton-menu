<?php

namespace App\Entity;

use App\Repository\SignalementRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SignalementRepository::class)
 */
class Signalement
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=Comment::class, cascade={"persist", "remove"})
     */
    private $message;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nb_signalement;

    /**
     * @ORM\Column(type="boolean")
     */
    private $traiter;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $create_at;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $update_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): ?Comment
    {
        return $this->message;
    }

    public function setMessage(?Comment $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getNbSignalement(): ?string
    {
        return $this->nb_signalement;
    }

    public function setNbSignalement(string $nb_signalement): self
    {
        $this->nb_signalement = $nb_signalement;

        return $this;
    }

    public function getTraiter(): ?bool
    {
        return $this->traiter;
    }

    public function setTraiter(bool $traiter): self
    {
        $this->traiter = $traiter;

        return $this;
    }

    public function getCreateAt(): ?\DateTimeImmutable
    {
        return $this->create_at;
    }

    public function setCreateAt(\DateTimeImmutable $create_at): self
    {
        $this->create_at = $create_at;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeImmutable
    {
        return $this->update_at;
    }

    public function setUpdateAt(\DateTimeImmutable $update_at): self
    {
        $this->update_at = $update_at;

        return $this;
    }
}
