<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\Public;

use App\Resume\Application\DTO\Experience\ExperienceDto;
use App\Resume\Application\Projection\ResumeEntryNormalizerTrait;
use App\Resume\Application\Resource\ExperienceResource;
use App\Resume\Domain\Entity\Experience;
use App\General\Infrastructure\ValueObject\SymfonyUser;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

use function array_key_exists;
use function is_string;

/**
 * Class CreateExperienceController
 */
#[AsController]
#[Route('/platform/resume/experience', name: 'resume_api_platform_experience_')]
class CreateExperienceController extends AbstractController
{
    use JsonRequestTrait;
    use ResumeEntryNormalizerTrait;

    public function __construct(
        private readonly ExperienceResource $experienceResource,
    ) {
    }

    #[Route('/create', name: 'post', methods: ['POST'])]
    #[OA\Post(
        summary: 'Create experience entry',
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
            return new JsonResponse([
                'message' => 'Missing resume identifier.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $company = isset($payload['company']) ? (string)$payload['company'] : '';
        $role = isset($payload['role']) ? (string)$payload['role'] : '';
        $startDate = isset($payload['startDate']) ? (string)$payload['startDate'] : '';

        if ($company === '' || $role === '' || $startDate === '') {
            return new JsonResponse([
                'message' => 'Company, role and start date are required.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $dto = new ExperienceDto();
        $dto->applySymfonyUser($symfonyUser);
        $dto->setResumeId($payload['resumeId']);
        $dto->setCompany($company);
        $dto->setRole($role);
        $dto->setStartDate($startDate);

        if (array_key_exists('endDate', $payload)) {
            $dto->setEndDate($payload['endDate'] !== null ? (string)$payload['endDate'] : null);
        }

        if (array_key_exists('isCurrent', $payload)) {
            $dto->setIsCurrent((bool)$payload['isCurrent']);
        }

        if (array_key_exists('position', $payload)) {
            $dto->setPosition($payload['position'] !== null ? (int)$payload['position'] : null);
        }

        if (array_key_exists('location', $payload)) {
            $dto->setLocation($payload['location'] !== null ? (string)$payload['location'] : null);
        }

        if (array_key_exists('description', $payload)) {
            $dto->setDescription($payload['description'] !== null ? (string)$payload['description'] : null);
        }

        /** @var Experience $experience */
        $experience = $this->experienceResource->create($dto);

        return new JsonResponse($this->normalizeExperience($experience), Response::HTTP_CREATED);
    }
}
