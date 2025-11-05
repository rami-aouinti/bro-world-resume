<?php

declare(strict_types=1);

namespace App\Resume\Application\DTO\Skill;

use Bro\WorldCoreBundle\Application\DTO\Interfaces\SymfonyUserAwareDtoInterface;
use Bro\WorldCoreBundle\Application\DTO\RestDto;
use Bro\WorldCoreBundle\Domain\Entity\Interfaces\EntityInterface;
use Bro\WorldCoreBundle\Domain\ValueObject\UserId;
use Bro\WorldCoreBundle\Infrastructure\ValueObject\SymfonyUser;
use App\Resume\Domain\Entity\Resume;
use App\Resume\Domain\Entity\Skill as SkillEntity;
use Override;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SkillDto
 */
class SkillDto extends RestDto implements SymfonyUserAwareDtoInterface
{
    protected static array $mappings = [
        'userId' => 'updateUserId',
        'resumeId' => 'updateResumeId',
    ];

    #[Assert\NotBlank]
    #[Assert\Uuid]
    protected ?string $userId = null;

    #[Assert\NotBlank]
    #[Assert\Uuid]
    protected ?string $resumeId = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    protected ?string $name = null;

    #[Assert\Length(max: 255)]
    protected ?string $category = null;

    #[Assert\Length(max: 50)]
    protected ?string $level = null;

    protected ?int $position = null;

    #[Override]
    public function applySymfonyUser(SymfonyUser $symfonyUser): void
    {
        $this->setUserId($symfonyUser->getUserIdentifier());
    }

    public function getResumeId(): ?string
    {
        return $this->resumeId;
    }

    public function setResumeId(?string $resumeId): self
    {
        $this->setVisited('resumeId');
        $this->resumeId = $resumeId;

        return $this;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(?string $userId): self
    {
        $this->setVisited('userId');
        $this->userId = $userId;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->setVisited('name');
        $this->name = $name;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): self
    {
        $this->setVisited('category');
        $this->category = $category;

        return $this;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(?string $level): self
    {
        $this->setVisited('level');
        $this->level = $level;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->setVisited('position');
        $this->position = $position;

        return $this;
    }

    #[Override]
    public function load(EntityInterface $entity): self
    {
        if (!$entity instanceof SkillEntity) {
            return $this;
        }

        $this->userId = (string)$entity->getUserId();
        $this->resumeId = $entity->getResume()?->getId();
        $this->name = $entity->getName();
        $this->category = $entity->getCategory();
        $this->level = $entity->getLevel();
        $this->position = $entity->getPosition();

        return $this;
    }

    public function applyResumeRelationship(SkillEntity $skill, Resume $resume): void
    {
        $skill->setResume($resume);
    }

    protected function updateResumeId(SkillEntity $skill, ?string $value): void
    {
        // Resume association is handled in SkillResource::ensureResumeAssociation().
        // This method exists to prevent the base RestDto from calling a non-existent setter.
    }

    protected function updateUserId(SkillEntity $skill, ?string $value): void
    {
        if ($value === null) {
            return;
        }

        $skill->setUserId(new UserId($value));
    }
}
