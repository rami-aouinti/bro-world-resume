<?php

declare(strict_types=1);

namespace App\Resume\Application\Message\Handler;

use App\Resume\Application\Message\Command\CreateResumeMessage;
use App\Resume\Application\Resource\ResumeResource;
use App\Resume\Domain\Entity\Resume;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Class CreateResumeMessageHandler
 */
#[AsMessageHandler(bus: 'command_bus')]
readonly class CreateResumeMessageHandler
{
    public function __construct(
        private ResumeResource $resource
    ) {
    }

    public function __invoke(CreateResumeMessage $message): Resume
    {
        /** @var Resume $resume */
        $resume = $this->resource->handleCreate(
            $message->dto,
            $message->flush,
            $message->skipValidation,
            $message->entityManagerName
        );

        return $resume;
    }
}
