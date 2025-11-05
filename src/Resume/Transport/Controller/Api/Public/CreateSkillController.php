<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\Public;

use App\Resume\Application\DTO\Skill\SkillDto;
use App\Resume\Application\Projection\ResumeEntryNormalizerTrait;
use App\Resume\Application\Resource\SkillResource;
use App\Resume\Application\Service\SetupResume;
use App\Resume\Domain\Entity\Skill;
use Bro\WorldCoreBundle\Infrastructure\ValueObject\SymfonyUser;
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
 * Class CreateSkillController
 */
#[AsController]
#[Route('/platform/resume/skill', name: 'resume_api_platform_skill_')]
class CreateSkillController extends AbstractController
{
    use JsonRequestTrait;
    use ResumeEntryNormalizerTrait;

    public function __construct(
        private readonly SkillResource $skillResource,
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
        summary: 'Create skill entry',
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
                'message' => 'Skill name is required.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $dto = new SkillDto();
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

        /** @var Skill $skill */
        $skill = $this->skillResource->create($dto);

        return new JsonResponse($this->normalizeSkill($skill), Response::HTTP_CREATED);
    }
}
