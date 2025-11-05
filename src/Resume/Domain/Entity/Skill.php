<?php

declare(strict_types=1);

namespace App\Resume\Domain\Entity;

use Bro\WorldCoreBundle\Domain\Entity\Interfaces\EntityInterface;
use Bro\WorldCoreBundle\Domain\Entity\Traits\Timestampable;
use Bro\WorldCoreBundle\Domain\Entity\Traits\Uuid;
use Bro\WorldCoreBundle\Domain\Rest\UuidHelper;
use Bro\WorldCoreBundle\Domain\ValueObject\UserId;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Throwable;

/**
 * Class Skill
 */
#[ORM\Entity]
#[ORM\Table(name: 'resume_skill')]
class Skill implements EntityInterface
{
    use Timestampable;
    use Uuid;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid_binary_ordered_time')]
    private UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: Resume::class, inversedBy: 'skills')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Resume $resume = null;

    #[ORM\Column(type: 'uuid_binary_ordered_time')]
    private UuidInterface $userId;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank]
    private string $name = '';

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $category = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $level = null;

    #[ORM\Column(type: Types::INTEGER)]
    private int $position = 0;

    /**
     * @throws Throwable
     */
    public function __construct()
    {
        $this->id = $this->createUuid();
        $this->userId = $this->createUuid();
    }

    public function getId(): string
    {
        return $this->id->toString();
    }

    public function getResume(): ?Resume
    {
        return $this->resume;
    }

    public function setResume(?Resume $resume): self
    {
        $this->resume = $resume;

        if ($resume !== null) {
            $this->userId = UuidHelper::fromString((string)$resume->getUserId());
        }

        return $this;
    }

    public function getUserId(): UserId
    {
        return new UserId($this->userId->toString());
    }

    public function setUserId(UserId $userId): self
    {
        $this->userId = UuidHelper::fromString((string)$userId);

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(?string $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }
}
