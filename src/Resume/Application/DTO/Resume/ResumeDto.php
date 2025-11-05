<?php

declare(strict_types=1);

namespace App\Resume\Application\DTO\Resume;

use Bro\WorldCoreBundle\Application\DTO\Interfaces\SymfonyUserAwareDtoInterface;
use Bro\WorldCoreBundle\Application\DTO\RestDto;
use Bro\WorldCoreBundle\Domain\Entity\Interfaces\EntityInterface;
use Bro\WorldCoreBundle\Domain\ValueObject\UserId;
use Bro\WorldCoreBundle\Infrastructure\ValueObject\SymfonyUser;
use App\Resume\Domain\Entity\Resume as ResumeEntity;
use Override;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ResumeDto
 */
class ResumeDto extends RestDto implements SymfonyUserAwareDtoInterface
{
    protected static array $mappings = [
        'userId' => 'updateUserId',
    ];

    #[Assert\NotBlank]
    #[Assert\Uuid]
    protected ?string $userId = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    protected ?string $fullName = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    protected ?string $headline = null;

    #[Assert\Length(max: 255)]
    protected ?string $location = null;

    #[Assert\Email]
    #[Assert\Length(max: 255)]
    protected ?string $email = null;

    #[Assert\Length(max: 64)]
    protected ?string $phone = null;

    #[Assert\Url]
    #[Assert\Length(max: 255)]
    protected ?string $website = null;

    #[Assert\Url]
    #[Assert\Length(max: 255)]
    protected ?string $avatarUrl = null;

    protected ?string $summary = null;

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

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(?string $fullName): self
    {
        $this->setVisited('fullName');
        $this->fullName = $fullName;

        return $this;
    }

    public function getHeadline(): ?string
    {
        return $this->headline;
    }

    public function setHeadline(?string $headline): self
    {
        $this->setVisited('headline');
        $this->headline = $headline;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->setVisited('location');
        $this->location = $location;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->setVisited('email');
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->setVisited('phone');
        $this->phone = $phone;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): self
    {
        $this->setVisited('website');
        $this->website = $website;

        return $this;
    }

    public function getAvatarUrl(): ?string
    {
        return $this->avatarUrl;
    }

    public function setAvatarUrl(?string $avatarUrl): self
    {
        $this->setVisited('avatarUrl');
        $this->avatarUrl = $avatarUrl;

        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): self
    {
        $this->setVisited('summary');
        $this->summary = $summary;

        return $this;
    }

    #[Override]
    public function load(EntityInterface $entity): self
    {
        if (!$entity instanceof ResumeEntity) {
            return $this;
        }

        $this->userId = (string)$entity->getUserId();
        $this->fullName = $entity->getFullName();
        $this->headline = $entity->getHeadline();
        $this->location = $entity->getLocation();
        $this->email = $entity->getEmail();
        $this->phone = $entity->getPhone();
        $this->website = $entity->getWebsite();
        $this->avatarUrl = $entity->getAvatarUrl();
        $this->summary = $entity->getSummary();

        return $this;
    }

    protected function updateUserId(ResumeEntity $resume, ?string $value): void
    {
        if ($value === null) {
            return;
        }

        $resume->setUserId(new UserId($value));
    }
}
