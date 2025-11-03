<?php

declare(strict_types=1);

namespace App\Resume\Application\Message\Handler;

use App\Resume\Application\Message\Command\CreateProjectMessage;
use App\Resume\Application\Resource\ProjectResource;
use App\Resume\Domain\Entity\Project;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command_bus')]
class CreateProjectMessageHandler
{
    public function __construct(
        private readonly ProjectResource $resource
    ) {
    }

    public function __invoke(CreateProjectMessage $message): Project
    {
        /** @var Project $project */
        $project = $this->resource->handleCreate(
            $message->dto,
            $message->flush,
            $message->skipValidation,
            $message->entityManagerName
        );

        return $project;
    }
}
