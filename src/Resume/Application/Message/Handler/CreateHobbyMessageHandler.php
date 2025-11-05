<?php

declare(strict_types=1);

namespace App\Resume\Application\Message\Handler;

use App\Resume\Application\Message\Command\CreateHobbyMessage;
use App\Resume\Application\Resource\HobbyResource;
use App\Resume\Domain\Entity\Hobby;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Class CreateHobbyMessageHandler
 */
#[AsMessageHandler(bus: 'command_bus')]
readonly class CreateHobbyMessageHandler
{
    public function __construct(
        private HobbyResource $resource
    ) {
    }

    public function __invoke(CreateHobbyMessage $message): Hobby
    {
        /** @var Hobby $hobby */
        $hobby = $this->resource->handleCreate(
            $message->dto,
            $message->flush,
            $message->skipValidation,
            $message->entityManagerName
        );

        return $hobby;
    }
}
