<?php

namespace App\Entity;

use App\Repository\RecipeTagsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RecipeTagsRepository::class)
 */
class RecipeTags
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=150, unique=true)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=RecipeTagsLinks::class, mappedBy="recipeTag", orphanRemoval=true)
     */
    private $recipeTagsLinks;

    public function __construct()
    {
        $this->recipeTagsLinks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|RecipeTagsLinks[]
     */
    public function getRecipeTagsLinks(): Collection
    {
        return $this->recipeTagsLinks;
    }

    public function addRecipeTagsLink(RecipeTagsLinks $recipeTagsLink): self
    {
        if (!$this->recipeTagsLinks->contains($recipeTagsLink)) {
            $this->recipeTagsLinks[] = $recipeTagsLink;
            $recipeTagsLink->setRecipeTag($this);
        }

        return $this;
    }

    public function removeRecipeTagsLink(RecipeTagsLinks $recipeTagsLink): self
    {
        if ($this->recipeTagsLinks->removeElement($recipeTagsLink)) {
            // set the owning side to null (unless already changed)
            if ($recipeTagsLink->getRecipeTag() === $this) {
                $recipeTagsLink->setRecipeTag(null);
            }
        }

        return $this;
    }
}
