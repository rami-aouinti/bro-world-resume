<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\Public;

use App\Resume\Application\Resource\HobbyResource;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DeleteHobbyController
 */
#[AsController]
#[Route('/platform/resume/hobby', name: 'resume_api_platform_hobby_')]
class DeleteHobbyController extends AbstractController
{
    public function __construct(
        private readonly HobbyResource $hobbyResource,
    ) {
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Delete hobby entry',
    )]
    public function __invoke(string $id): JsonResponse
    {
        $this->hobbyResource->delete($id);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
