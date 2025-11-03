<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\Public;

use App\Resume\Application\Resource\ExperienceResource;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DeleteExperienceController
 */
#[AsController]
#[Route('/platform/resume/experience', name: 'resume_api_platform_experience_')]
class DeleteExperienceController extends AbstractController
{
    public function __construct(
        private readonly ExperienceResource $experienceResource,
    ) {
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Delete experience entry',
    )]
    public function __invoke(string $id): JsonResponse
    {
        $this->experienceResource->delete($id);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
