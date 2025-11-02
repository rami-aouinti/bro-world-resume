<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\Public;

use App\General\Domain\ValueObject\UserId;
use App\General\Transport\Rest\Interfaces\ResponseHandlerInterface;
use App\Resume\Application\Projection\ResumeProjectionService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid as SymfonyUuid;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Route(path: '/api/public/resume')]
class ResumePublicController
{
    public function __construct(
        private readonly ResumeProjectionService $projectionService,
        private readonly ResponseHandlerInterface $responseHandler,
    ) {
    }

    #[Route(path: '/{userId}', requirements: ['userId' => SymfonyUuid::RFC_4122], methods: [Request::METHOD_GET])]
    public function profile(Request $request, string $userId): Response
    {
        $payload = $this->projectionService->getResumeProfile(new UserId($userId));

        if ($payload === null) {
            throw new NotFoundHttpException('Resume not found for provided userId.');
        }

        return $this->responseHandler->createResponse($request, $payload, null, Response::HTTP_OK);
    }

    #[Route(path: '/{userId}/experiences', requirements: ['userId' => SymfonyUuid::RFC_4122], methods: [Request::METHOD_GET])]
    public function experiences(Request $request, string $userId): Response
    {
        $profile = $this->projectionService->getResumeProfile(new UserId($userId));

        if ($profile === null) {
            throw new NotFoundHttpException('Resume not found for provided userId.');
        }

        return $this->responseHandler->createResponse($request, $profile['experiences'], null, Response::HTTP_OK);
    }

    #[Route(path: '/{userId}/education', requirements: ['userId' => SymfonyUuid::RFC_4122], methods: [Request::METHOD_GET])]
    public function education(Request $request, string $userId): Response
    {
        $profile = $this->projectionService->getResumeProfile(new UserId($userId));

        if ($profile === null) {
            throw new NotFoundHttpException('Resume not found for provided userId.');
        }

        return $this->responseHandler->createResponse($request, $profile['education'], null, Response::HTTP_OK);
    }

    #[Route(path: '/{userId}/skills', requirements: ['userId' => SymfonyUuid::RFC_4122], methods: [Request::METHOD_GET])]
    public function skills(Request $request, string $userId): Response
    {
        $profile = $this->projectionService->getResumeProfile(new UserId($userId));

        if ($profile === null) {
            throw new NotFoundHttpException('Resume not found for provided userId.');
        }

        return $this->responseHandler->createResponse($request, $profile['skills'], null, Response::HTTP_OK);
    }
}
