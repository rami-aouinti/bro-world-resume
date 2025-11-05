<?php

declare(strict_types=1);

namespace App\Resume\Application\ApiProxy;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function in_array;

/**
 * @package App\Resume\Application\ApiProxy
 * @author  Rami Aouinti
 */
readonly class UserProxy
{
    public function __construct(
        private HttpClientInterface $httpClient,
    ) {
    }

    /**
     * Retrieves all users from the external API.
     *
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function getUsers(): array
    {
        $response = $this->httpClient->request('GET', 'https://bro-world.org/api/v1/user', [
            'headers' => [
                'Authorization' => 'ApiKey agYybuBZFsjXaCKBfjFWa2qFYMUshXZWFcz575KT',
            ],
        ]);

        return $response->toArray();
    }
}
