<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\Public;

use Bro\WorldCoreBundle\Infrastructure\ValueObject\SymfonyUser;
use App\Resume\Application\DTO\Project\ProjectDto;
use App\Resume\Application\Projection\ResumeEntryNormalizerTrait;
use App\Resume\Application\Resource\ProjectResource;
use App\Resume\Domain\Entity\Project;
use App\Resume\Transport\Controller\Api\Public\JsonRequestTrait;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

use function array_key_exists;

/**
 * Class EditProjectController
 */
#[AsController]
#[Route('/platform/resume/project', name: 'resume_api_platform_project_')]
class EditProjectController extends AbstractController
{
    use JsonRequestTrait;
    use ResumeEntryNormalizerTrait;

    public function __construct(
        private readonly ProjectResource $projectResource,
    ) {
    }

    #[Route('/{id}/edit', name: 'patch', methods: ['PATCH'])]
    #[OA\Patch(
        summary: 'Edit project entry',
    )]
    public function __invoke(string $id, SymfonyUser $symfonyUser, Request $request): JsonResponse
    {
        try {
            $payload = $this->decodeJsonPayload($request);
        } catch (BadRequestHttpException $exception) {
            return new JsonResponse([
                'message' => $exception->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($payload === []) {
            return new JsonResponse([
                'message' => 'No data provided for update.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $dto = new ProjectDto();
        $dto->applySymfonyUser($symfonyUser);

        if (array_key_exists('resumeId', $payload)) {
            $dto->setResumeId($payload['resumeId'] !== null ? (string)$payload['resumeId'] : null);
        }

        if (array_key_exists('title', $payload)) {
            $dto->setTitle($payload['title'] !== null ? (string)$payload['title'] : null);
        }

        if (array_key_exists('description', $payload)) {
            $dto->setDescription($payload['description'] !== null ? (string)$payload['description'] : null);
        }

        if (array_key_exists('logoUrl', $payload)) {
            $dto->setLogoUrl($payload['logoUrl'] !== null ? (string)$payload['logoUrl'] : null);
        }

        if (array_key_exists('urlDemo', $payload)) {
            $dto->setUrlDemo($payload['urlDemo'] !== null ? (string)$payload['urlDemo'] : null);
        }

        if (array_key_exists('urlRepository', $payload)) {
            $dto->setUrlRepository($payload['urlRepository'] !== null ? (string)$payload['urlRepository'] : null);
        }

        if (array_key_exists('status', $payload)) {
            $dto->setStatus($payload['status'] !== null ? (string)$payload['status'] : null);
        }

        if (array_key_exists('position', $payload)) {
            $dto->setPosition($payload['position'] !== null ? (int)$payload['position'] : null);
        }

        /** @var Project $project */
        $project = $this->projectResource->patch($id, $dto);

        return new JsonResponse($this->normalizeProject($project));
    }
}
