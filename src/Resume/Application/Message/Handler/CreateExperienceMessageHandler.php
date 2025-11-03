<?php

declare(strict_types=1);

namespace App\Resume\Application\Message\Handler;

use App\Resume\Application\Message\Command\CreateExperienceMessage;
use App\Resume\Application\Resource\ExperienceResource;
use App\Resume\Domain\Entity\Experience;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command_bus')]
class CreateExperienceMessageHandler
{
    public function __construct(
        private readonly ExperienceResource $resource
    ) {
    }

    public function __invoke(CreateExperienceMessage $message): Experience
    {
        /** @var Experience $experience */
        $experience = $this->resource->handleCreate(
            $message->dto,
            $message->flush,
            $message->skipValidation,
            $message->entityManagerName
        );

        return $experience;
    }
}
