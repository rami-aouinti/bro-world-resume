<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\Public;

use App\Resume\Application\Resource\ProjectResource;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DeleteProjectController
 */
#[AsController]
#[Route('/platform/resume/project', name: 'resume_api_platform_project_')]
class DeleteProjectController extends AbstractController
{
    public function __construct(
        private readonly ProjectResource $projectResource,
    ) {
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Delete project entry',
    )]
    public function __invoke(string $id): JsonResponse
    {
        $this->projectResource->delete($id);

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
