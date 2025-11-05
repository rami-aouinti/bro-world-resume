<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\Public;

use App\Resume\Application\DTO\Education\EducationDto;
use App\Resume\Application\Projection\ResumeEntryNormalizerTrait;
use App\Resume\Application\Resource\EducationResource;
use App\Resume\Application\Service\SetupResume;
use App\Resume\Domain\Entity\Education;
use Bro\WorldCoreBundle\Infrastructure\ValueObject\SymfonyUser;
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
 * Class CreateEducationController
 */
#[AsController]
#[Route('/platform/resume/education', name: 'resume_api_platform_education_')]
class CreateEducationController extends AbstractController
{
    use JsonRequestTrait;
    use ResumeEntryNormalizerTrait;

    public function __construct(
        private readonly EducationResource $educationResource,
        private readonly SetupResume $setupResume,
    ) {
    }

    /**
     * @throws Throwable
     */
    #[Route('/create', name: 'post', methods: ['POST'])]
    #[OA\Post(
        summary: 'Create education entry',
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

        $school = isset($payload['school']) ? (string)$payload['school'] : '';

        if ($school === '') {
            return new JsonResponse([
                'message' => 'School name is required.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $dto = new EducationDto();
        $dto->applySymfonyUser($symfonyUser);
        $dto->setResumeId($payload['resumeId']);
        $dto->setSchool($school);

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
        $education = $this->educationResource->create($dto);

        return new JsonResponse($this->normalizeEducation($education), Response::HTTP_CREATED);
    }
}
