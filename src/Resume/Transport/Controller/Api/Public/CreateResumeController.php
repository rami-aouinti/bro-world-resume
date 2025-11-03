<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\Public;

use App\General\Domain\ValueObject\UserId;
use App\General\Infrastructure\ValueObject\SymfonyUser;
use App\Resume\Domain\Repository\ResumeRepositoryInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ReviewStatsController
 *
 * @package App\Tool\Transport\Controller\Api
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[AsController]
#[Route('/platform/resume', name: 'resume_api_platform_')]
class CreateResumeController extends AbstractController
{


    public function __construct(
        private readonly ResumeRepositoryInterface $resumeRepository,
    )
    {
    }

    #[Route('/create', name: 'post', methods: ['POST'])]
    #[OA\Get(
        summary: 'Post resume',
    )]
    public function __invoke(SymfonyUser $symfonyUser, Request $request): JsonResponse
    {

    }
}
