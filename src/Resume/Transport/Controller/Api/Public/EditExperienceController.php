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

/**
 * Class EditExperienceController
 */
#[AsController]
#[Route('/platform/resume/experience', name: 'resume_api_platform_experience_')]
class EditExperienceController extends AbstractController
{
    use JsonRequestTrait;
    use ResumeEntryNormalizerTrait;

    public function __construct(
        private readonly ExperienceResource $experienceResource,
    ) {
    }

    #[Route('/{id}/edit', name: 'patch', methods: ['PATCH'])]
    #[OA\Patch(
        summary: 'Edit experience entry',
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

        $dto = new ExperienceDto();
        $dto->applySymfonyUser($symfonyUser);

        if (array_key_exists('resumeId', $payload)) {
            $dto->setResumeId($payload['resumeId'] !== null ? (string)$payload['resumeId'] : null);
        }

        if (array_key_exists('company', $payload)) {
            $dto->setCompany($payload['company'] !== null ? (string)$payload['company'] : null);
        }

        if (array_key_exists('role', $payload)) {
            $dto->setRole($payload['role'] !== null ? (string)$payload['role'] : null);
        }

        if (array_key_exists('startDate', $payload)) {
            $dto->setStartDate($payload['startDate'] !== null ? (string)$payload['startDate'] : null);
        }

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

        if (array_key_exists('companyLocation', $payload)) {
            $dto->setCompanyLocation($payload['companyLocation'] !== null ? (string)$payload['companyLocation'] : null);
        }

        if (array_key_exists('companyLogo', $payload)) {
            $dto->setCompanyLogo($payload['companyLogo'] !== null ? (string)$payload['companyLogo'] : null);
        }

        if (array_key_exists('description', $payload)) {
            $dto->setDescription($payload['description'] !== null ? (string)$payload['description'] : null);
        }

        /** @var Experience $experience */
        $experience = $this->experienceResource->patch($id, $dto);

        return new JsonResponse($this->normalizeExperience($experience));
    }
}
