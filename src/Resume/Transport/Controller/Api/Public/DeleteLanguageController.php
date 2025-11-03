<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\Public;

use App\Resume\Application\Resource\LanguageResource;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DeleteLanguageController
 */
#[AsController]
#[Route('/platform/resume/language', name: 'resume_api_platform_language_')]
class DeleteLanguageController extends AbstractController
{
    public function __construct(
        private readonly LanguageResource $languageResource,
    ) {
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Delete language entry',
    )]
    public function __invoke(string $id): JsonResponse
    {
        $this->languageResource->delete($id);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
