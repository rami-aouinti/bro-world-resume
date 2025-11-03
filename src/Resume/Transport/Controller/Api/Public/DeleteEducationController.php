<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\Public;

use App\Resume\Application\Resource\EducationResource;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DeleteEducationController
 */
#[AsController]
#[Route('/platform/resume/education', name: 'resume_api_platform_education_')]
class DeleteEducationController extends AbstractController
{
    public function __construct(
        private readonly EducationResource $educationResource,
    ) {
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Delete education entry',
    )]
    public function __invoke(string $id): JsonResponse
    {
        $this->educationResource->delete($id);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
