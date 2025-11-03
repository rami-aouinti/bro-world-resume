<?php

declare(strict_types=1);

namespace App\Resume\Application\Message\Handler;

use App\Resume\Application\Message\Command\CreateLanguageMessage;
use App\Resume\Application\Resource\LanguageResource;
use App\Resume\Domain\Entity\Language;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command_bus')]
class CreateLanguageMessageHandler
{
    public function __construct(
        private readonly LanguageResource $resource
    ) {
    }

    public function __invoke(CreateLanguageMessage $message): Language
    {
        /** @var Language $language */
        $language = $this->resource->handleCreate(
            $message->dto,
            $message->flush,
            $message->skipValidation,
            $message->entityManagerName
        );

        return $language;
    }
}
