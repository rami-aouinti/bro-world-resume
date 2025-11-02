<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\Public;

use App\General\Infrastructure\ValueObject\SymfonyUser;
use App\Resume\Domain\Repository\ResumeRepositoryInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
class GetResumeController extends AbstractController
{


    public function __construct(
        private readonly ResumeRepositoryInterface $resumeRepository,
    )
    {
    }

    #[Route('/', name: 'get', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get resume',
    )]
    public function __invoke(SymfonyUser $symfonyUser): JsonResponse
    {
        $resume = $this->resumeRepository->findOneByUserId($symfonyUser->getUserIdentifier());

        return new JsonResponse([
            'resume' => $resume
        ]);
    }
}
