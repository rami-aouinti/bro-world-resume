<?php

declare(strict_types=1);

namespace App\Resume\Domain\Entity;

use App\General\Domain\Entity\Interfaces\EntityInterface;
use App\General\Domain\Entity\Traits\Timestampable;
use App\General\Domain\Entity\Traits\Uuid;
use App\General\Domain\Rest\UuidHelper;
use App\General\Domain\ValueObject\UserId;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Throwable;

/**
 * Class Education
 */
#[ORM\Entity]
#[ORM\Table(name: 'resume_education')]
class Education implements EntityInterface
{
    use Timestampable;
    use Uuid;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid_binary_ordered_time')]
    private UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: Resume::class, inversedBy: 'educations')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Resume $resume = null;

    #[ORM\Column(type: 'uuid_binary_ordered_time')]
    private UuidInterface $userId;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank]
    private string $school = '';

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $degree = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $field = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $schoolLocation = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Url]
    private ?string $schoolLogo = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $startDate = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $endDate = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isCurrent = false;

    #[ORM\Column(type: Types::INTEGER)]
    private int $position = 0;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

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

    public function getSchool(): string
    {
        return $this->school;
    }

    public function setSchool(string $school): self
    {
        $this->school = $school;

        return $this;
    }

    public function getDegree(): ?string
    {
        return $this->degree;
    }

    public function setDegree(?string $degree): self
    {
        $this->degree = $degree;

        return $this;
    }

    public function getField(): ?string
    {
        return $this->field;
    }

    public function setField(?string $field): self
    {
        $this->field = $field;

        return $this;
    }

    public function getSchoolLocation(): ?string
    {
        return $this->schoolLocation;
    }

    public function setSchoolLocation(?string $schoolLocation): self
    {
        $this->schoolLocation = $schoolLocation;

        return $this;
    }

    public function getSchoolLogo(): ?string
    {
        return $this->schoolLogo;
    }

    public function setSchoolLogo(?string $schoolLogo): self
    {
        $this->schoolLogo = $schoolLogo;

        return $this;
    }

    public function getStartDate(): ?DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(?DateTimeImmutable $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(?DateTimeImmutable $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function isCurrent(): bool
    {
        return $this->isCurrent;
    }

    public function setIsCurrent(bool $isCurrent): self
    {
        $this->isCurrent = $isCurrent;

        if ($isCurrent) {
            $this->endDate = null;
        }

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
