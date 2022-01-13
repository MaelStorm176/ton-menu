<?php

namespace App\Entity;

use App\Repository\RecipeTagsLinksRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RecipeTagsLinksRepository::class)
 */
class RecipeTagsLinks
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Recipe::class, inversedBy="recipeTagsLinks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $recipe;

    /**
     * @ORM\ManyToOne(targetEntity=RecipeTags::class, inversedBy="recipeTagsLinks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $recipeTag;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRecipe(): ?Recipe
    {
        return $this->recipe;
    }

    public function setRecipe(?Recipe $recipe): self
    {
        $this->recipe = $recipe;

        return $this;
    }

    public function getRecipeTag(): ?RecipeTags
    {
        return $this->recipeTag;
    }

    public function setRecipeTag(?RecipeTags $recipeTag): self
    {
        $this->recipeTag = $recipeTag;

        return $this;
    }
}
