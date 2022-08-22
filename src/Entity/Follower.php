<?php

namespace App\Entity;

use App\Repository\FollowerRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FollowerRepository::class)
 */
class Follower
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $user_id;

    /**
     * @ORM\Column(type="integer")
     */
    private $chef_id;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $follow_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getChefId(): ?int
    {
        return $this->chef_id;
    }

    public function setChefId(int $chef_id): self
    {
        $this->chef_id = $chef_id;

        return $this;
    }

    public function getFollowAt(): ?\DateTimeImmutable
    {
        return $this->follow_at;
    }

    public function setFollowAt(\DateTimeImmutable $follow_at): self
    {
        $this->follow_at = $follow_at;

        return $this;
    }
}
