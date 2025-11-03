<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\Public;

use App\Resume\Application\DTO\Education\EducationDto;
use App\Resume\Application\Projection\ResumeEntryNormalizerTrait;
use App\Resume\Application\Resource\EducationResource;
use App\Resume\Domain\Entity\Education;
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
 * Class EditEducationController
 */
#[AsController]
#[Route('/platform/resume/education', name: 'resume_api_platform_education_')]
class EditEducationController extends AbstractController
{
    use JsonRequestTrait;
    use ResumeEntryNormalizerTrait;

    public function __construct(
        private readonly EducationResource $educationResource,
    ) {
    }

    #[Route('/{id}/edit', name: 'patch', methods: ['PATCH'])]
    #[OA\Patch(
        summary: 'Edit education entry',
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

        $dto = new EducationDto();
        $dto->applySymfonyUser($symfonyUser);

        if (array_key_exists('resumeId', $payload)) {
            $dto->setResumeId($payload['resumeId'] !== null ? (string)$payload['resumeId'] : null);
        }

        if (array_key_exists('school', $payload)) {
            $dto->setSchool($payload['school'] !== null ? (string)$payload['school'] : null);
        }

        if (array_key_exists('degree', $payload)) {
            $dto->setDegree($payload['degree'] !== null ? (string)$payload['degree'] : null);
        }

        if (array_key_exists('field', $payload)) {
            $dto->setField($payload['field'] !== null ? (string)$payload['field'] : null);
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

        if (array_key_exists('schoolLocation', $payload)) {
            $dto->setSchoolLocation($payload['schoolLocation'] !== null ? (string)$payload['schoolLocation'] : null);
        }

        if (array_key_exists('schoolLogo', $payload)) {
            $dto->setSchoolLogo($payload['schoolLogo'] !== null ? (string)$payload['schoolLogo'] : null);
        }

        if (array_key_exists('description', $payload)) {
            $dto->setDescription($payload['description'] !== null ? (string)$payload['description'] : null);
        }

        /** @var Education $education */
        $education = $this->educationResource->patch($id, $dto);

        return new JsonResponse($this->normalizeEducation($education));
    }
}
