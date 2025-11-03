<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\Public;

use App\General\Domain\ValueObject\UserId;
use App\Resume\Application\Projection\ResumeProjectionService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route('/public/resume', name: 'resume_api_public_')]
class GetResumeController extends AbstractController
{
    public function __construct(
        private readonly ResumeProjectionService $resumeProjectionService,
    ) {
    }

    #[Route('/{userId}', name: 'get', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get public resume profile',
    )]
    public function __invoke(string $userId): JsonResponse
    {
        $profile = $this->resumeProjectionService->getResumeProfile(new UserId($userId));

        if ($profile === null) {
            throw $this->createNotFoundException('Resume profile not found.');
        }

        return $this->json($profile);
    }

    #[Route('/{userId}/experiences', name: 'experiences', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get public resume experiences',
    )]
    public function experiences(string $userId): JsonResponse
    {
        $experiences = $this->resumeProjectionService->getExperiences(new UserId($userId));

        return $this->json($experiences);
    }

    #[Route('/{userId}/education', name: 'education', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get public resume education',
    )]
    public function education(string $userId): JsonResponse
    {
        $education = $this->resumeProjectionService->getEducation(new UserId($userId));

        return $this->json($education);
    }

    #[Route('/{userId}/skills', name: 'skills', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get public resume skills',
    )]
    public function skills(string $userId): JsonResponse
    {
        $skills = $this->resumeProjectionService->getSkills(new UserId($userId));

        return $this->json($skills);
    }

    #[Route('/{userId}/languages', name: 'languages', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get public resume languages',
    )]
    public function languages(string $userId): JsonResponse
    {
        $languages = $this->resumeProjectionService->getLanguages(new UserId($userId));

        return $this->json($languages);
    }

    #[Route('/{userId}/hobbies', name: 'hobbies', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get public resume hobbies',
    )]
    public function hobbies(string $userId): JsonResponse
    {
        $hobbies = $this->resumeProjectionService->getHobbies(new UserId($userId));

        return $this->json($hobbies);
    }
}
