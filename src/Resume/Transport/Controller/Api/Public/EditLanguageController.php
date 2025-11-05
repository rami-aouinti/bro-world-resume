<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\Public;

use App\Resume\Application\DTO\Language\LanguageDto;
use App\Resume\Application\Projection\ResumeEntryNormalizerTrait;
use App\Resume\Application\Resource\LanguageResource;
use App\Resume\Domain\Entity\Language;
use Bro\WorldCoreBundle\Infrastructure\ValueObject\SymfonyUser;
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
 * Class EditLanguageController
 */
#[AsController]
#[Route('/platform/resume/language', name: 'resume_api_platform_language_')]
class EditLanguageController extends AbstractController
{
    use JsonRequestTrait;
    use ResumeEntryNormalizerTrait;

    public function __construct(
        private readonly LanguageResource $languageResource,
    ) {
    }

    #[Route('/{id}/edit', name: 'patch', methods: ['PATCH'])]
    #[OA\Patch(
        summary: 'Edit language entry',
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

        $dto = new LanguageDto();
        $dto->applySymfonyUser($symfonyUser);

        if (array_key_exists('resumeId', $payload)) {
            $dto->setResumeId($payload['resumeId'] !== null ? (string)$payload['resumeId'] : null);
        }

        if (array_key_exists('name', $payload)) {
            $dto->setName($payload['name'] !== null ? (string)$payload['name'] : null);
        }

        if (array_key_exists('category', $payload)) {
            $dto->setCategory($payload['category'] !== null ? (string)$payload['category'] : null);
        }

        if (array_key_exists('level', $payload)) {
            $dto->setLevel($payload['level'] !== null ? (string)$payload['level'] : null);
        }

        if (array_key_exists('position', $payload)) {
            $dto->setPosition($payload['position'] !== null ? (int)$payload['position'] : null);
        }

        /** @var Language $language */
        $language = $this->languageResource->patch($id, $dto);

        return new JsonResponse($this->normalizeLanguage($language));
    }
}
