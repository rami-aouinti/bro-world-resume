<?php

declare(strict_types=1);

namespace App\Resume\Application\DTO\Experience;

use Bro\WorldCoreBundle\Application\DTO\Interfaces\SymfonyUserAwareDtoInterface;
use Bro\WorldCoreBundle\Application\DTO\RestDto;
use Bro\WorldCoreBundle\Domain\Entity\Interfaces\EntityInterface;
use Bro\WorldCoreBundle\Domain\ValueObject\UserId;
use Bro\WorldCoreBundle\Infrastructure\ValueObject\SymfonyUser;
use App\Resume\Domain\Entity\Experience as ExperienceEntity;
use App\Resume\Domain\Entity\Resume;
use DateMalformedStringException;
use DateTimeImmutable;
use Override;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ExperienceDto
 */
class ExperienceDto extends RestDto implements SymfonyUserAwareDtoInterface
{
    protected static array $mappings = [
        'userId' => 'updateUserId',
        'resumeId' => 'updateResumeId',
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

    protected ?int $position = null;

    #[Assert\Length(max: 255)]
    protected ?string $location = null;

    #[Assert\Length(max: 255)]
    protected ?string $companyLocation = null;

    #[Assert\Url]
    #[Assert\Length(max: 255)]
    protected ?string $companyLogo = null;

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

    public function setCompanyLocation(?string $companyLocation): self
    {
        $this->setVisited('companyLocation');
        $this->companyLocation = $companyLocation;

        return $this;
    }

    public function getCompanyLocation(): ?string
    {
        return $this->companyLocation;
    }

    public function setCompanyLogo(?string $companyLogo): self
    {
        $this->setVisited('companyLogo');
        $this->companyLogo = $companyLogo;

        return $this;
    }

    public function getCompanyLogo(): ?string
    {
        return $this->companyLogo;
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
        $this->companyLocation = $entity->getCompanyLocation();
        $this->companyLogo = $entity->getCompanyLogo();
        $this->description = $entity->getDescription();

        return $this;
    }

    public function applyResumeRelationship(ExperienceEntity $experience, Resume $resume): void
    {
        $experience->setResume($resume);
    }

    protected function updateResumeId(ExperienceEntity $experience, ?string $value): void
    {
        // Resume association is handled in ExperienceResource::ensureResumeAssociation().
        // This method exists to prevent the base RestDto from calling a non-existent setter.
    }

    protected function updateUserId(ExperienceEntity $experience, ?string $value): void
    {
        if ($value === null) {
            return;
        }

        $experience->setUserId(new UserId($value));
    }

    /**
     * @throws DateMalformedStringException
     */
    protected function updateStartDate(ExperienceEntity $experience, ?string $value): void
    {
        if ($value === null) {
            return;
        }

        $experience->setStartDate(new DateTimeImmutable($value));
    }

    /**
     * @throws DateMalformedStringException
     */
    protected function updateEndDate(ExperienceEntity $experience, ?string $value): void
    {
        if ($value === null || $value === '') {
            $experience->setEndDate(null);

            return;
        }

        $experience->setEndDate(new DateTimeImmutable($value));
    }
}
