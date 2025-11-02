<?php

declare(strict_types=1);

namespace App\Resume\Application\DTO\Education;

use App\General\Application\DTO\Interfaces\SymfonyUserAwareDtoInterface;
use App\General\Application\DTO\RestDto;
use App\General\Domain\Entity\Interfaces\EntityInterface;
use App\General\Domain\ValueObject\UserId;
use App\General\Infrastructure\ValueObject\SymfonyUser;
use App\Resume\Domain\Entity\Education as EducationEntity;
use App\Resume\Domain\Entity\Resume;
use DateTimeImmutable;
use Override;
use Symfony\Component\Validator\Constraints as Assert;

class EducationDto extends RestDto implements SymfonyUserAwareDtoInterface
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
    protected ?string $school = null;

    #[Assert\Length(max: 255)]
    protected ?string $degree = null;

    #[Assert\Length(max: 255)]
    protected ?string $field = null;

    #[Assert\Date]
    protected ?string $startDate = null;

    #[Assert\Date]
    protected ?string $endDate = null;

    protected ?bool $isCurrent = null;

    protected ?int $position = null;

    protected ?string $description = null;

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

    public function getSchool(): ?string
    {
        return $this->school;
    }

    public function setSchool(?string $school): self
    {
        $this->setVisited('school');
        $this->school = $school;

        return $this;
    }

    public function getDegree(): ?string
    {
        return $this->degree;
    }

    public function setDegree(?string $degree): self
    {
        $this->setVisited('degree');
        $this->degree = $degree;

        return $this;
    }

    public function getField(): ?string
    {
        return $this->field;
    }

    public function setField(?string $field): self
    {
        $this->setVisited('field');
        $this->field = $field;

        return $this;
    }

    public function getStartDate(): ?string
    {
        return $this->startDate;
    }

    public function setStartDate(?string $startDate): self
    {
        $this->setVisited('startDate');
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?string
    {
        return $this->endDate;
    }

    public function setEndDate(?string $endDate): self
    {
        $this->setVisited('endDate');
        $this->endDate = $endDate;

        return $this;
    }

    public function isCurrent(): ?bool
    {
        return $this->isCurrent;
    }

    public function setIsCurrent(?bool $isCurrent): self
    {
        $this->setVisited('isCurrent');
        $this->isCurrent = $isCurrent;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->setVisited('description');
        $this->description = $description;

        return $this;
    }

    #[Override]
    public function load(EntityInterface $entity): self
    {
        if (!$entity instanceof EducationEntity) {
            return $this;
        }

        $this->userId = (string)$entity->getUserId();
        $this->resumeId = $entity->getResume()?->getId();
        $this->school = $entity->getSchool();
        $this->degree = $entity->getDegree();
        $this->field = $entity->getField();
        $this->startDate = $entity->getStartDate()?->format('Y-m-d');
        $this->endDate = $entity->getEndDate()?->format('Y-m-d');
        $this->isCurrent = $entity->isCurrent();
        $this->position = $entity->getPosition();
        $this->description = $entity->getDescription();

        return $this;
    }

    protected function updateUserId(EducationEntity $education, ?string $value): void
    {
        if ($value === null) {
            return;
        }

        $education->setUserId(new UserId($value));
    }

    protected function updateStartDate(EducationEntity $education, ?string $value): void
    {
        if ($value === null || $value === '') {
            $education->setStartDate(null);

            return;
        }

        $education->setStartDate(new DateTimeImmutable($value));
    }

    protected function updateEndDate(EducationEntity $education, ?string $value): void
    {
        if ($value === null || $value === '') {
            $education->setEndDate(null);

            return;
        }

        $education->setEndDate(new DateTimeImmutable($value));
    }

    public function applyResumeRelationship(EducationEntity $education, Resume $resume): void
    {
        $education->setResume($resume);
    }
}
