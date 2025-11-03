<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\Public;

use App\Resume\Application\Resource\SkillResource;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DeleteSkillController
 */
#[AsController]
#[Route('/platform/resume/skill', name: 'resume_api_platform_skill_')]
class DeleteSkillController extends AbstractController
{
    public function __construct(
        private readonly SkillResource $skillResource,
    ) {
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Delete skill entry',
    )]
    public function __invoke(string $id): JsonResponse
    {
        $this->skillResource->delete($id);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
