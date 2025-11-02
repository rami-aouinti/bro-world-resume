<?php

declare(strict_types=1);

namespace App\Resume\Application\Resource;

use App\General\Application\Rest\RestResource;
use App\General\Domain\ValueObject\UserId;
use App\Resume\Application\DTO\Resume\ResumeDto;
use App\Resume\Domain\Entity\Resume;
use App\Resume\Domain\Repository\ResumeRepositoryInterface;

class ResumeResource extends RestResource
{
    public function __construct(private readonly ResumeRepositoryInterface $resumeRepository)
    {
        parent::__construct($resumeRepository);
        $this->setDtoClass(ResumeDto::class);
    }

    public function getRepository(): ResumeRepositoryInterface
    {
        /** @var ResumeRepositoryInterface $repository */
        $repository = parent::getRepository();

        return $repository;
    }

    public function findOneByUserId(UserId $userId): ?Resume
    {
        return $this->resumeRepository->findOneByUserId($userId);
    }

}
