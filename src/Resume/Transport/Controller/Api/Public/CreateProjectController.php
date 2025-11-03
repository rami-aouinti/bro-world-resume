<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\Public;

use App\General\Infrastructure\ValueObject\SymfonyUser;
use App\Resume\Application\DTO\Project\ProjectDto;
use App\Resume\Application\Projection\ResumeEntryNormalizerTrait;
use App\Resume\Application\Resource\ProjectResource;
use App\Resume\Application\Service\SetupResume;
use App\Resume\Domain\Entity\Project;
use App\Resume\Transport\Controller\Api\Public\JsonRequestTrait;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

use function array_key_exists;
use function is_string;

/**
 * Class CreateProjectController
 */
#[AsController]
#[Route('/platform/resume/project', name: 'resume_api_platform_project_')]
class CreateProjectController extends AbstractController
{
    use JsonRequestTrait;
    use ResumeEntryNormalizerTrait;

    public function __construct(
        private readonly ProjectResource $projectResource,
        private readonly SetupResume $setupResume,
    ) {
    }

    /**
     * @throws OptimisticLockException
     * @throws Throwable
     * @throws ORMException
     */
    #[Route('/create', name: 'post', methods: ['POST'])]
    #[OA\Post(
        summary: 'Create project entry',
    )]
    public function __invoke(SymfonyUser $symfonyUser, Request $request): JsonResponse
    {
        try {
            $payload = $this->decodeJsonPayload($request);
        } catch (BadRequestHttpException $exception) {
            return new JsonResponse([
                'message' => $exception->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!isset($payload['resumeId']) || !is_string($payload['resumeId'])) {
            $payload['resumeId'] = $this->setupResume->initResume($symfonyUser);
        }

        $title = isset($payload['title']) ? (string)$payload['title'] : '';

        if ($title === '') {
            return new JsonResponse([
                'message' => 'Project title is required.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $dto = new ProjectDto();
        $dto->applySymfonyUser($symfonyUser);
        $dto->setResumeId($payload['resumeId']);
        $dto->setTitle($title);

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
        $project = $this->projectResource->create($dto);

        return new JsonResponse($this->normalizeProject($project), Response::HTTP_CREATED);
    }
}
