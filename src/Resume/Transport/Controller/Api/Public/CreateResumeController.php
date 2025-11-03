<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\Public;

use App\General\Domain\Utils\JSON;
use App\General\Domain\ValueObject\UserId;
use App\General\Infrastructure\ValueObject\SymfonyUser;
use App\Resume\Domain\Entity\Resume;
use App\Resume\Domain\Repository\ResumeRepositoryInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use JsonException;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

use function is_array;

/**
 * Class ReviewStatsController
 *
 * @package App\Tool\Transport\Controller\Api
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
#[AsController]
#[Route('/platform/resume', name: 'resume_api_platform_')]
class CreateResumeController extends AbstractController
{
    public function __construct(
        private readonly ResumeRepositoryInterface $resumeRepository,
    ) {
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    #[Route('/create', name: 'post', methods: ['POST'])]
    #[OA\Post(
        summary: 'Post resume',
    )]
    public function __invoke(SymfonyUser $symfonyUser, Request $request): JsonResponse
    {
        try {
            $payload = JSON::decode((string)$request->getContent(), true);
        } catch (JsonException) {
            return new JsonResponse([
                'message' => 'Invalid JSON body.',
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!is_array($payload)) {
            return new JsonResponse([
                'message' => 'Invalid request payload.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $fullName = isset($payload['fullName']) ? (string)$payload['fullName'] : '';
        $headline = isset($payload['headline']) ? (string)$payload['headline'] : '';

        if ($fullName === '' || $headline === '') {
            return new JsonResponse([
                'message' => 'Missing required fields.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $userId = new UserId($symfonyUser->getUserIdentifier());
        $resume = $this->resumeRepository->findOneByUserId($userId);

        if (!$resume instanceof Resume) {
            $resume = new Resume();
        }

        $resume
            ->setUserId($userId)
            ->setFullName($fullName)
            ->setHeadline($headline)
            ->setSummary($payload['summary'] ?? null)
            ->setLocation($payload['location'] ?? null)
            ->setEmail($payload['email'] ?? null)
            ->setPhone($payload['phone'] ?? null)
            ->setWebsite($payload['website'] ?? null)
            ->setAvatarUrl($payload['avatarUrl'] ?? null);

        $this->resumeRepository->save($resume);

        return new JsonResponse($this->normalizeResume($resume), Response::HTTP_CREATED);
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeResume(Resume $resume): array
    {
        return [
            'id' => $resume->getId(),
            'userId' => (string)$resume->getUserId(),
            'fullName' => $resume->getFullName(),
            'headline' => $resume->getHeadline(),
            'summary' => $resume->getSummary(),
            'location' => $resume->getLocation(),
            'email' => $resume->getEmail(),
            'phone' => $resume->getPhone(),
            'website' => $resume->getWebsite(),
            'avatarUrl' => $resume->getAvatarUrl(),
            'updatedAt' => $resume->getUpdatedAt()?->format(DATE_ATOM),
            'createdAt' => $resume->getCreatedAt()?->format(DATE_ATOM),
        ];
    }
}
