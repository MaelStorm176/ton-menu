<?php

namespace App\Entity;

use App\Repository\RecipeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RecipeRepository::class)
 */
class Recipe
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $type;

    /**
     * @ORM\Column(type="smallint")
     */
    private $number_of_persons;

    /**
     * @ORM\Column(type="float")
     */
    private $difficulty;

    /**
     * @ORM\Column(type="time")
     */
    private $preparation_time;

    /**
     * @ORM\Column(type="time")
     */
    private $total_time;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="recipes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user_id;

    /**
     * @ORM\OneToMany(targetEntity=Rating::class, mappedBy="recette")
     */
    private $ratings;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $updated_at;

    /**
     * @ORM\OneToMany(targetEntity=RecipeSteps::class, mappedBy="recipe", orphanRemoval=true, cascade={"persist"})
     */
    private $recipeSteps;

    /**
     * @ORM\OneToMany(targetEntity=RecipeIngredients::class, mappedBy="recipe", orphanRemoval=true)
     */
    private $ingredients;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="recette")
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity=RecipeImages::class, mappedBy="recipe", orphanRemoval=true)
     */
    private $recipeImages;

    /**
     * @ORM\Column(type="integer")
     */
    private $budget;

    /**
     * @ORM\ManyToMany(targetEntity=RecipeTags::class, mappedBy="recipe")
     */
    private $recipeTags;

    /**
     * @ORM\OneToMany(targetEntity=RecipeQuantities::class, mappedBy="recipe", orphanRemoval=true)
     */
    private $recipeQuantities;
  
    public function __construct()
    {
        $this->recipeSteps = new ArrayCollection();
        $this->ingredients = new ArrayCollection();
        $this->ratings = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->recipeImages = new ArrayCollection();
        $this->recipeTags = new ArrayCollection();
        $this->recipeQuantities = new ArrayCollection();
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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getNumberOfPersons(): ?int
    {
        return $this->number_of_persons;
    }

    public function setNumberOfPersons(int $number_of_persons): self
    {
        $this->number_of_persons = $number_of_persons;

        return $this;
    }

    public function getDifficulty(): ?float
    {
        return $this->difficulty;
    }

    public function setDifficulty(float $difficulty): self
    {
        $this->difficulty = $difficulty;

        return $this;
    }

    public function getUserId(): ?User
    {
        return $this->user_id;
    }

    public function setUserId(?User $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**
     * @return Collection|Rating[]
     */
    public function getRatings(): Collection
    {
        return $this->ratings;
    }

    /**
     * @param Rating $rating
     * @return $this
     */
    public function addRating(Rating $rating): self
    {
        if (!$this->ratings->contains($rating)) {
            $this->ratings[] = $rating;
            $rating->setRecette($this);
        }

        return $this;
    }

    public function getAverageRating(): ?float
    {
        $ratings = $this->getRatings();
        $sum = 0;
        foreach ($ratings as $rating) {
            $sum += $rating->getRate();
        }
        if (count($ratings) > 0) {
            return round(floatval($sum / count($ratings)), 1);
        } else {
            return null;
        }
    }

    public function getMaxRating(): ?float
    {
        $ratings = $this->getRatings();
        $max = 0;
        foreach ($ratings as $rating) {
            if ($rating->getRate() > $max) {
                $max = $rating->getRate();
            }
        }
        return $max;
    }

    public function getNumberOfRating(): ?int
    {
        return count($this->getRatings());
    }

    public function getPreparationTimeToTime(): ?string
    {
        return $this->secondsToTime($this->preparation_time*60);
    }

    public function getPreparationTime(): \DateTime
    {
        return $this->preparation_time;
    }

    public function setPreparationTime(\DateTime $preparation_time): self
    {
      $this->preparation_time = $preparation_time;

      return $this;
    }
      
    public function removeRating(Rating $rating): self
    {
        if ($this->ratings->removeElement($rating)) {
            // set the owning side to null (unless already changed)
            if ($rating->getRecette() === $this) {
                $rating->setRecette(null);
            }
        }

        return $this;
    }

    public function getTotalTime(): ?\DateTime
    {
        return $this->total_time;
    }

    public function setTotalTime(\DateTime $total_time): self
    {
        $this->total_time = $total_time;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setRecette($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getRecette() === $this) {
                $comment->setRecette(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|RecipeSteps[]
     */
    public function getRecipeSteps(): Collection
    {
        return $this->recipeSteps;
    }

    public function addRecipeStep(RecipeSteps $recipeStep): self
    {
        if (!$this->recipeSteps->contains($recipeStep)) {
            $this->recipeSteps[] = $recipeStep;
            $recipeStep->setRecipe($this);
        }

        return $this;
    }

    public function removeRecipeStep(RecipeSteps $recipeStep): self
    {
        if ($this->recipeSteps->removeElement($recipeStep)) {
            // set the owning side to null (unless already changed)
            if ($recipeStep->getRecipe() === $this) {
                $recipeStep->setRecipe(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|RecipeIngredients[]
     */
    public function getIngredients(): Collection
    {
        return $this->ingredients;
    }

    public function addIngredient(RecipeIngredients $ingredient): self
    {
        if (!$this->ingredients->contains($ingredient)) {
            $this->ingredients[] = $ingredient;
            $ingredient->setRecipe($this);
        }

        return $this;
    }

    public function removeIngredient(RecipeIngredients $ingredient): self
    {
        if ($this->ingredients->removeElement($ingredient)) {
            // set the owning side to null (unless already changed)
            if ($ingredient->getRecipe() === $this) {
                $ingredient->setRecipe(null);
            }
        }

        return $this;
    }

    private function secondsToTime($seconds) {
        $dtF = new \DateTime('@0');
        $dtT = new \DateTime("@$seconds");
        if ($seconds >= 86400){
            return $dtF->diff($dtT)->format('%a j %h h %i min');
        }else{
            return $dtF->diff($dtT)->format('%h h %i min');
        }
    }

    /**
     * @return Collection<int, RecipeImages>
     */
    public function getRecipeImages(): Collection
    {
        return $this->recipeImages;
    }

    public function addRecipeImage(RecipeImages $recipeImage): self
    {
        if (!$this->recipeImages->contains($recipeImage)) {
            $this->recipeImages[] = $recipeImage;
            $recipeImage->setRecipe($this);
        }

        return $this;
    }

    public function removeRecipeImage(RecipeImages $recipeImage): self
    {
        if ($this->recipeImages->removeElement($recipeImage)) {
            // set the owning side to null (unless already changed)
            if ($recipeImage->getRecipe() === $this) {
                $recipeImage->setRecipe(null);
            }
        }

        return $this;
    }

    public function getBudget(): ?int
    {
        return $this->budget;
    }

    public function setBudget(int $budget): self
    {
        $this->budget = $budget;

        return $this;
    }

    /**
     * @return Collection<int, RecipeTags>
     */
    public function getRecipeTags(): Collection
    {
        return $this->recipeTags;
    }

    public function addRecipeTag(RecipeTags $recipeTag): self
    {
        if (!$this->recipeTags->contains($recipeTag)) {
            $this->recipeTags[] = $recipeTag;
            $recipeTag->addRecipe($this);
        }

        return $this;
    }

    public function removeRecipeTag(RecipeTags $recipeTag): self
    {
        if ($this->recipeTags->removeElement($recipeTag)) {
            $recipeTag->removeRecipe($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, RecipeQuantities>
     */
    public function getRecipeQuantities(): Collection
    {
        return $this->recipeQuantities;
    }

    public function addRecipeQuantity(RecipeQuantities $recipeQuantity): self
    {
        if (!$this->recipeQuantities->contains($recipeQuantity)) {
            $this->recipeQuantities[] = $recipeQuantity;
            $recipeQuantity->setRecipe($this);
        }

        return $this;
    }

    public function removeRecipeQuantity(RecipeQuantities $recipeQuantity): self
    {
        if ($this->recipeQuantities->removeElement($recipeQuantity)) {
            // set the owning side to null (unless already changed)
            if ($recipeQuantity->getRecipe() === $this) {
                $recipeQuantity->setRecipe(null);
            }
        }

        return $this;
    }
}
