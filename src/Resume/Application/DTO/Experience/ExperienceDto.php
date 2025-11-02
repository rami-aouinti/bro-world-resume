<?php

declare(strict_types=1);

namespace App\Resume\Application\DTO\Experience;

use App\General\Application\DTO\Interfaces\SymfonyUserAwareDtoInterface;
use App\General\Application\DTO\RestDto;
use App\General\Domain\Entity\Interfaces\EntityInterface;
use App\General\Domain\ValueObject\UserId;
use App\General\Infrastructure\ValueObject\SymfonyUser;
use App\Resume\Domain\Entity\Experience as ExperienceEntity;
use App\Resume\Domain\Entity\Resume;
use DateTimeImmutable;
use Override;
use Symfony\Component\Validator\Constraints as Assert;

class ExperienceDto extends RestDto implements SymfonyUserAwareDtoInterface
{
    protected static array $mappings = [
        'userId' => 'updateUserId',
        'startDate' => 'updateStartDate',
        'endDate' => 'updateEndDate',
    ];

    #[Assert\NotBlank]
    #[Assert\Uuid]
    protected ?string $userId = null;

    #[Assert\NotBlank]
    #[Assert\Uuid]
    protected ?string $resumeId = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    protected ?string $company = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    protected ?string $role = null;

    #[Assert\Date]
    protected ?string $startDate = null;

    #[Assert\Date]
    protected ?string $endDate = null;

    protected ?bool $isCurrent = null;

    #[Override]
    public function applySymfonyUser(SymfonyUser $symfonyUser): void
    {
        $this->setUserId($symfonyUser->getUserIdentifier());
    }

    protected ?int $position = null;

    #[Assert\Length(max: 255)]
    protected ?string $location = null;

    protected ?string $description = null;

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

    public function setUserId(?string $userId): self
    {
        $this->setVisited('userId');
        $this->userId = $userId;

        return $this;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setCompany(?string $company): self
    {
        $this->setVisited('company');
        $this->company = $company;

        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setRole(?string $role): self
    {
        $this->setVisited('role');
        $this->role = $role;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setStartDate(?string $startDate): self
    {
        $this->setVisited('startDate');
        $this->startDate = $startDate;

        return $this;
    }

    public function getStartDate(): ?string
    {
        return $this->startDate;
    }

    public function setEndDate(?string $endDate): self
    {
        $this->setVisited('endDate');
        $this->endDate = $endDate;

        return $this;
    }

    public function getEndDate(): ?string
    {
        return $this->endDate;
    }

    public function setIsCurrent(?bool $isCurrent): self
    {
        $this->setVisited('isCurrent');
        $this->isCurrent = $isCurrent;

        return $this;
    }

    public function isCurrent(): ?bool
    {
        return $this->isCurrent;
    }

    public function setPosition(?int $position): self
    {
        $this->setVisited('position');
        $this->position = $position;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setLocation(?string $location): self
    {
        $this->setVisited('location');
        $this->location = $location;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setDescription(?string $description): self
    {
        $this->setVisited('description');
        $this->description = $description;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    #[Override]
    public function load(EntityInterface $entity): self
    {
        if (!$entity instanceof ExperienceEntity) {
            return $this;
        }

        $this->userId = (string)$entity->getUserId();
        $this->resumeId = $entity->getResume()?->getId();
        $this->company = $entity->getCompany();
        $this->role = $entity->getRole();
        $this->startDate = $entity->getStartDate()->format('Y-m-d');
        $this->endDate = $entity->getEndDate()?->format('Y-m-d');
        $this->isCurrent = $entity->isCurrent();
        $this->position = $entity->getPosition();
        $this->location = $entity->getLocation();
        $this->description = $entity->getDescription();

        return $this;
    }

    protected function updateUserId(ExperienceEntity $experience, ?string $value): void
    {
        if ($value === null) {
            return;
        }

        $experience->setUserId(new UserId($value));
    }

    protected function updateStartDate(ExperienceEntity $experience, ?string $value): void
    {
        if ($value === null) {
            return;
        }

        $experience->setStartDate(new DateTimeImmutable($value));
    }

    protected function updateEndDate(ExperienceEntity $experience, ?string $value): void
    {
        if ($value === null || $value === '') {
            $experience->setEndDate(null);

            return;
        }

        $experience->setEndDate(new DateTimeImmutable($value));
    }

    public function applyResumeRelationship(ExperienceEntity $experience, Resume $resume): void
    {
        $experience->setResume($resume);
    }
}
