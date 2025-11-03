<?php

declare(strict_types=1);

namespace App\Resume\Application\DTO\Project;

use App\General\Application\DTO\Interfaces\SymfonyUserAwareDtoInterface;
use App\General\Application\DTO\RestDto;
use App\General\Domain\Entity\Interfaces\EntityInterface;
use App\General\Domain\ValueObject\UserId;
use App\General\Infrastructure\ValueObject\SymfonyUser;
use App\Resume\Domain\Entity\Project as ProjectEntity;
use App\Resume\Domain\Entity\Resume;
use Override;
use Symfony\Component\Validator\Constraints as Assert;

class ProjectDto extends RestDto implements SymfonyUserAwareDtoInterface
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
    protected ?string $title = null;

    protected ?string $description = null;

    #[Assert\Url]
    #[Assert\Length(max: 255)]
    protected ?string $logoUrl = null;

    #[Assert\Url]
    #[Assert\Length(max: 255)]
    protected ?string $urlDemo = null;

    #[Assert\Url]
    #[Assert\Length(max: 255)]
    protected ?string $urlRepository = null;

    #[Assert\Choice(choices: [ProjectEntity::STATUS_PUBLIC, ProjectEntity::STATUS_PRIVATE])]
    protected ?string $status = null;

    protected ?int $position = null;

    #[Override]
    public function applySymfonyUser(SymfonyUser $symfonyUser): void
    {
        $this->setUserId($symfonyUser->getUserIdentifier());
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->setVisited('title');
        $this->title = $title;

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

    public function getLogoUrl(): ?string
    {
        return $this->logoUrl;
    }

    public function setLogoUrl(?string $logoUrl): self
    {
        $this->setVisited('logoUrl');
        $this->logoUrl = $logoUrl;

        return $this;
    }

    public function getUrlDemo(): ?string
    {
        return $this->urlDemo;
    }

    public function setUrlDemo(?string $urlDemo): self
    {
        $this->setVisited('urlDemo');
        $this->urlDemo = $urlDemo;

        return $this;
    }

    public function getUrlRepository(): ?string
    {
        return $this->urlRepository;
    }

    public function setUrlRepository(?string $urlRepository): self
    {
        $this->setVisited('urlRepository');
        $this->urlRepository = $urlRepository;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->setVisited('status');
        $this->status = $status;

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
        if (!$entity instanceof ProjectEntity) {
            return $this;
        }

        $this->userId = (string)$entity->getUserId();
        $this->resumeId = $entity->getResume()?->getId();
        $this->title = $entity->getTitle();
        $this->description = $entity->getDescription();
        $this->logoUrl = $entity->getLogoUrl();
        $this->urlDemo = $entity->getUrlDemo();
        $this->urlRepository = $entity->getUrlRepository();
        $this->status = $entity->getStatus();
        $this->position = $entity->getPosition();

        return $this;
    }

    public function applyResumeRelationship(ProjectEntity $project, Resume $resume): void
    {
        $project->setResume($resume);
    }

    protected function updateResumeId(ProjectEntity $project, ?string $value): void
    {
        // Resume association handled explicitly in ProjectResource::ensureResumeAssociation().
    }

    protected function updateUserId(ProjectEntity $project, ?string $value): void
    {
        if ($value === null) {
            return;
        }

        $project->setUserId(new UserId($value));
    }
}
