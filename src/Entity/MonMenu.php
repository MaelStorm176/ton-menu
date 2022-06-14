<?php

namespace App\Entity;

use App\Repository\MonMenuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MonMenuRepository::class)
 */
class MonMenu
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="json")
     */
    private $menu_save = [];

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="monMenu")
     */
    private $user;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $date_generate;

    public function __construct()
    {
        $this->user = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMenuSave(): array
    {
        return $this->menu_save;
    }

    public function setMenuSave(array $menu_save): self
    {
        $this->menu_save = $menu_save;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUser(): Collection
    {
        return $this->user;
    }

    public function addUser(User $user): self
    {
        if (!$this->user->contains($user)) {
            $this->user[] = $user;
            $user->setMonMenu($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->user->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getMonMenu() === $this) {
                $user->setMonMenu(null);
            }
        }

        return $this;
    }

    public function getDateGenerate(): ?\DateTimeImmutable
    {
        return $this->date_generate;
    }

    public function setDateGenerate(\DateTimeImmutable $date_generate): self
    {
        $this->date_generate = $date_generate;

        return $this;
    }
}
