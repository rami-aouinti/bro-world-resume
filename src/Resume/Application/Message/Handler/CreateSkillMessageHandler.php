<?php

declare(strict_types=1);

namespace App\Resume\Application\Message\Handler;

use App\Resume\Application\Message\Command\CreateSkillMessage;
use App\Resume\Application\Resource\SkillResource;
use App\Resume\Domain\Entity\Skill;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Class CreateSkillMessageHandler
 */
#[AsMessageHandler(bus: 'command_bus')]
readonly class CreateSkillMessageHandler
{
    public function __construct(
        private SkillResource $resource
    ) {
    }

    public function __invoke(CreateSkillMessage $message): Skill
    {
        /** @var Skill $skill */
        $skill = $this->resource->handleCreate(
            $message->dto,
            $message->flush,
            $message->skipValidation,
            $message->entityManagerName
        );

        return $skill;
    }
}
