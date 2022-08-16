<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Controller\Api\RandomMenuController;
use App\Controller\Api\RandomRecipeController;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\RecipeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    collectionOperations: [
        'get' => [
            'normalization_context' => [
                'groups' => ['recipe:list']
            ]
        ],
        'random' => [
            'method' => 'GET',
            'path' => '/recipes/random/{type}',
            'controller' => RandomRecipeController::class,
            'filters' => [],
            'normalization_context' => [
                'groups' => ['recipe:list']
            ],
            'openapi_context' => [
                'summary' => 'Get a random recipe',
                'description' => 'Get a random recipe',
                'parameters' => [
                    'type' => [
                        'in' => 'path',
                        'name' => 'type',
                        'description' => 'The type of recipe',
                        'required' => true,
                        'schema' => [
                            'type' => 'string'
                        ]
                    ]
                ]
            ]
        ],
        'menu' => [
            'method' => 'GET',
            'path' => '/recipes/menu/{number}',
            'controller' => RandomMenuController::class,
            'filters' => [],
            'normalization_context' => [
                'groups' => ['recipe:list']
            ],
            'openapi_context' => [
                'summary' => 'Get {number} of random menus',
                'description' => 'One menu is composed of 1 starter 1 main 1 dessert',
                'parameters' => [
                    'number' => [
                        'in' => 'path',
                        'name' => 'number',
                        'description' => 'number of menus',
                        'required' => true,
                        'minimum' => 1,
                        'maximum' => 7,
                        'schema' => [
                            'number' => 'number'
                        ]
                    ]
                ]
            ]
        ]
    ],
    itemOperations: [
        'get' => [
            'normalization_context' => [
                'groups' => ['recipe:item']
            ]
        ],
    ],
    attributes: [
        'pagination_enabled' => true,
        'pagination_items_per_page' => 5,
        'pagination_partial' => true,
        'order' => ['created_at' => 'DESC'],
    ]
)]
#[ApiFilter(SearchFilter::class, properties:["name" => "partial", "type" => "exact"])]
/**
 * @ORM\Entity(repositoryClass=RecipeRepository::class)
 */
class Recipe
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"recipe:list", "recipe:item"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"recipe:list", "recipe:item"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=20)
     * @Groups({"recipe:list", "recipe:item"})
     */
    private $type;

    /**
     * @ORM\Column(type="smallint")
     * @Groups({"recipe:list", "recipe:item"})
     */
    private $number_of_persons;

    /**
     * @ORM\Column(type="float")
     * @Groups({"recipe:list", "recipe:item"})
     */
    private $difficulty;

    /**
     * @ORM\Column(type="time")
     * @Groups({"recipe:list", "recipe:item"})
     */
    private $preparation_time;

    /**
     * @ORM\Column(type="time")
     * @Groups({"recipe:list", "recipe:item"})
     */
    private $total_time;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="recipes")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"recipe:list", "recipe:item"})
     */
    private $user_id;

    /**
     * @ORM\OneToMany(targetEntity=Rating::class, mappedBy="recette")
     */
    private $ratings;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"recipe:list", "recipe:item"})
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"recipe:list", "recipe:item"})
     */
    private $updated_at;

    /**
     * @ORM\OneToMany(targetEntity=RecipeSteps::class, mappedBy="recipe", orphanRemoval=true, cascade={"persist"})
     * @Groups({"recipe:list", "recipe:item"})
     */
    private $recipeSteps;

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
     * @Groups({"recipe:list", "recipe:item"})
     */
    private $budget;

    /**
     * @ORM\ManyToMany(targetEntity=RecipeTags::class, mappedBy="recipe")
     * @Groups({"recipe:list", "recipe:item"})
     */
    private $recipeTags;

    /**
     * @ORM\OneToMany(targetEntity=RecipeQuantities::class, mappedBy="recipe", orphanRemoval=true, cascade={"persist"})
     * @Groups({"recipe:list", "recipe:item"})
     */
    private $recipeQuantities;

    /**
     * @ORM\ManyToMany(targetEntity=Ingredient::class, inversedBy="recipes")
     * @ORM\JoinTable(name="recipe_ingredients")
     */
    private $ingredients;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"recipe:list", "recipe:item"})
     */
    private $description;
  
    public function __construct()
    {
        $this->recipeSteps = new ArrayCollection();
        $this->ratings = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->recipeImages = new ArrayCollection();
        $this->recipeTags = new ArrayCollection();
        $this->recipeQuantities = new ArrayCollection();
        $this->ingredients = new ArrayCollection();
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

    /**
     * @return Collection<int, Ingredient>
     */
    public function getIngredients(): Collection
    {
        return $this->ingredients;
    }

    public function addIngredient(Ingredient $ingredient): self
    {
        if (!$this->ingredients->contains($ingredient)) {
            $this->ingredients[] = $ingredient;
        }

        return $this;
    }

    public function removeIngredient(Ingredient $ingredient): self
    {
        $this->ingredients->removeElement($ingredient);

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'preparation_time' => $this->getPreparationTime(),
            'total_time' => $this->getTotalTime()->format('H:i'),
            'budget' => $this->getBudget(),
            'image' => $this->getRecipeImages()->getValues()[0]->getUrl(),
        ];
    }
}
