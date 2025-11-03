<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\Public;

use App\General\Domain\ValueObject\UserId;
use App\General\Infrastructure\ValueObject\SymfonyUser;
use App\Resume\Application\Projection\ResumeProjectionService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class GetResumeController
 */
#[AsController]
#[Route('/platform/resume', name: 'resume_api_platform_')]
class GetResumeController extends AbstractController
{
    public function __construct(
        private readonly ResumeProjectionService $resumeProjectionService,
    ) {
    }

    #[Route('/', name: 'get', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get public resume profile',
    )]
    public function __invoke(SymfonyUser $symfonyUser): JsonResponse
    {
        $profile = $this->resumeProjectionService->getResumeProfile(new UserId($symfonyUser->getUserIdentifier()));

        return $this->json($profile);
    }

    #[Route('/experiences', name: 'experiences', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get public resume experiences',
    )]
    public function experiences(SymfonyUser $symfonyUser): JsonResponse
    {
        $experiences = $this->resumeProjectionService->getExperiences(new UserId($symfonyUser->getUserIdentifier()));

        return $this->json($experiences);
    }

    #[Route('/education', name: 'education', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get public resume education',
    )]
    public function education(SymfonyUser $symfonyUser): JsonResponse
    {
        $education = $this->resumeProjectionService->getEducation(new UserId($symfonyUser->getUserIdentifier()));

        return $this->json($education);
    }

    #[Route('/skills', name: 'skills', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get public resume skills',
    )]
    public function skills(SymfonyUser $symfonyUser): JsonResponse
    {
        $skills = $this->resumeProjectionService->getSkills(new UserId($symfonyUser->getUserIdentifier()));

        return $this->json($skills);
    }

    #[Route('/languages', name: 'languages', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get public resume languages',
    )]
    public function languages(SymfonyUser $symfonyUser): JsonResponse
    {
        $languages = $this->resumeProjectionService->getLanguages(new UserId($symfonyUser->getUserIdentifier()));

        return $this->json($languages);
    }

    #[Route('/hobbies', name: 'hobbies', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get public resume hobbies',
    )]
    public function hobbies(SymfonyUser $symfonyUser): JsonResponse
    {
        $hobbies = $this->resumeProjectionService->getHobbies(new UserId($symfonyUser->getUserIdentifier()));

        return $this->json($hobbies);
    }

    #[Route('/projects', name: 'projects', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get public resume projects',
    )]
    public function projects(SymfonyUser $symfonyUser): JsonResponse
    {
        $projects = $this->resumeProjectionService->getProjects(new UserId($symfonyUser->getUserIdentifier()));

        return $this->json($projects);
    }
}
