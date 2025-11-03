<?php

declare(strict_types=1);

namespace App\Resume\Transport\Controller\Api\Public;

use App\General\Domain\Utils\JSON;
use JsonException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use function is_array;

trait JsonRequestTrait
{
    /**
     * @return array<string, mixed>
     */
    protected function decodeJsonPayload(Request $request): array
    {
        try {
            $payload = JSON::decode((string)$request->getContent(), true);
        } catch (JsonException) {
            throw new BadRequestHttpException('Invalid JSON body.');
        }

        if (!is_array($payload)) {
            throw new BadRequestHttpException('Invalid request payload.');
        }

        return $payload;
    }
}
