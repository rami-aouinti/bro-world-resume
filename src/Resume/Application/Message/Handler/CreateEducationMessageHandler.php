<?php

declare(strict_types=1);

namespace App\Resume\Application\Message\Handler;

use App\Resume\Application\Message\Command\CreateEducationMessage;
use App\Resume\Application\Resource\EducationResource;
use App\Resume\Domain\Entity\Education;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Class CreateEducationMessageHandler
 */
#[AsMessageHandler(bus: 'command_bus')]
readonly class CreateEducationMessageHandler
{
    public function __construct(
        private EducationResource $resource
    ) {
    }

    public function __invoke(CreateEducationMessage $message): Education
    {
        /** @var Education $education */
        $education = $this->resource->handleCreate(
            $message->dto,
            $message->flush,
            $message->skipValidation,
            $message->entityManagerName
        );

        return $education;
    }
}
