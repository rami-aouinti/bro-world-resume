<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\Public;

use App\General\Domain\ValueObject\UserId;
use App\General\Infrastructure\ValueObject\SymfonyUser;
use App\General\Transport\Rest\Interfaces\ResponseHandlerInterface;
use App\Resume\Application\Projection\ResumeProjectionService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Class ResumePublicController
 *
 */
#[Route(path: '/public/resume')]
readonly class ResumePublicController
{
    public function __construct(
        private ResumeProjectionService $projectionService,
        private ResponseHandlerInterface $responseHandler,
    ) {
    }

    #[Route(path: '/', methods: [Request::METHOD_GET])]
    public function profile(SymfonyUser $symfonyUser, Request $request): Response
    {
        $payload = $this->projectionService->getResumeProfile(new UserId($symfonyUser->getUserIdentifier()));

        if ($payload === null) {
            throw new NotFoundHttpException('Resume not found for provided userId.');
        }

        return $this->responseHandler->createResponse($request, $payload, null, Response::HTTP_OK);
    }

    #[Route(path: '/experiences', methods: [Request::METHOD_GET])]
    public function experiences(SymfonyUser $symfonyUser, Request $request): Response
    {
        $profile = $this->projectionService->getResumeProfile(new UserId($symfonyUser->getUserIdentifier()));

        if ($profile === null) {
            throw new NotFoundHttpException('Resume not found for provided userId.');
        }

        return $this->responseHandler->createResponse($request, $profile['experiences'], null, Response::HTTP_OK);
    }

    #[Route(path: '/education', methods: [Request::METHOD_GET])]
    public function education(SymfonyUser $symfonyUser, Request $request): Response
    {
        $profile = $this->projectionService->getResumeProfile(new UserId($symfonyUser->getUserIdentifier()));;

        if ($profile === null) {
            throw new NotFoundHttpException('Resume not found for provided userId.');
        }

        return $this->responseHandler->createResponse($request, $profile['education'], null, Response::HTTP_OK);
    }

    #[Route(path: '/skills', methods: [Request::METHOD_GET])]
    public function skills(SymfonyUser $symfonyUser, Request $request): Response
    {
        $profile = $this->projectionService->getResumeProfile(new UserId($symfonyUser->getUserIdentifier()));;

        if ($profile === null) {
            throw new NotFoundHttpException('Resume not found for provided userId.');
        }

        return $this->responseHandler->createResponse($request, $profile['skills'], null, Response::HTTP_OK);
    }
}
