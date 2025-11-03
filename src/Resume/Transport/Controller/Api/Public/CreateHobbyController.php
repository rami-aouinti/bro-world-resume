<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\Public;

use App\Resume\Application\DTO\Hobby\HobbyDto;
use App\Resume\Application\Projection\ResumeEntryNormalizerTrait;
use App\Resume\Application\Resource\HobbyResource;
use App\Resume\Application\Service\SetupResume;
use App\Resume\Domain\Entity\Hobby;
use App\General\Infrastructure\ValueObject\SymfonyUser;
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
 * Class CreateHobbyController
 */
#[AsController]
#[Route('/platform/resume/hobby', name: 'resume_api_platform_hobby_')]
class CreateHobbyController extends AbstractController
{
    use JsonRequestTrait;
    use ResumeEntryNormalizerTrait;

    public function __construct(
        private readonly HobbyResource $hobbyResource,
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
        summary: 'Create hobby entry',
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

        $name = isset($payload['name']) ? (string)$payload['name'] : '';

        if ($name === '') {
            return new JsonResponse([
                'message' => 'Hobby name is required.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $dto = new HobbyDto();
        $dto->applySymfonyUser($symfonyUser);
        $dto->setResumeId($payload['resumeId']);
        $dto->setName($name);

        if (array_key_exists('category', $payload)) {
            $dto->setCategory($payload['category'] !== null ? (string)$payload['category'] : null);
        }

        if (array_key_exists('level', $payload)) {
            $dto->setLevel($payload['level'] !== null ? (string)$payload['level'] : null);
        }

        if (array_key_exists('position', $payload)) {
            $dto->setPosition($payload['position'] !== null ? (int)$payload['position'] : null);
        }

        /** @var Hobby $hobby */
        $hobby = $this->hobbyResource->create($dto);

        return new JsonResponse($this->normalizeHobby($hobby), Response::HTTP_CREATED);
    }
}
