<?php

declare(strict_types=1);

namespace App\Resume\Domain\Entity;

use Bro\WorldCoreBundle\Domain\Entity\Interfaces\EntityInterface;
use Bro\WorldCoreBundle\Domain\Entity\Traits\PositionTrait;
use Bro\WorldCoreBundle\Domain\Entity\Traits\Timestampable;
use Bro\WorldCoreBundle\Domain\Entity\Traits\Uuid;
use Bro\WorldCoreBundle\Domain\Rest\UuidHelper;
use Bro\WorldCoreBundle\Domain\ValueObject\UserId;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

use Throwable;

use function in_array;

/**
 * Class Project
 */
#[ORM\Entity]
#[ORM\Table(name: 'resume_project')]
class Project implements EntityInterface
{
    use Timestampable;
    use Uuid;
    use PositionTrait;

    public const string STATUS_PUBLIC = 'public';
    public const string STATUS_PRIVATE = 'private';

    #[ORM\Id]
    #[ORM\Column(type: 'uuid_binary_ordered_time')]
    private UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: Resume::class, inversedBy: 'projects')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Resume $resume = null;

    #[ORM\Column(type: 'uuid_binary_ordered_time')]
    private UuidInterface $userId;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank]
    private string $title = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'logo_url', type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Url]
    private ?string $logoUrl = null;

    #[ORM\Column(name: 'url_demo', type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Url]
    private ?string $urlDemo = null;

    #[ORM\Column(name: 'url_repository', type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Url]
    private ?string $urlRepository = null;

    #[ORM\Column(type: Types::STRING, length: 20)]
    #[Assert\Choice(choices: [self::STATUS_PUBLIC, self::STATUS_PRIVATE])]
    private string $status = self::STATUS_PUBLIC;

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

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

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

    public function getLogoUrl(): ?string
    {
        return $this->logoUrl;
    }

    public function setLogoUrl(?string $logoUrl): self
    {
        $this->logoUrl = $logoUrl;

        return $this;
    }

    public function getUrlDemo(): ?string
    {
        return $this->urlDemo;
    }

    public function setUrlDemo(?string $urlDemo): self
    {
        $this->urlDemo = $urlDemo;

        return $this;
    }

    public function getUrlRepository(): ?string
    {
        return $this->urlRepository;
    }

    public function setUrlRepository(?string $urlRepository): self
    {
        $this->urlRepository = $urlRepository;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        if (!in_array($status, [self::STATUS_PUBLIC, self::STATUS_PRIVATE], true)) {
            $this->status = self::STATUS_PUBLIC;

            return $this;
        }

        $this->status = $status;

        return $this;
    }
}
