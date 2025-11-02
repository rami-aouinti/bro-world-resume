<?php

declare(strict_types=1);

namespace App\Tests\Application\Resume\Transport\Controller\Api\V1;

use App\Resume\Infrastructure\DataFixtures\ResumeFixtures;
use App\Tests\TestCase\WebTestCase;
use Ramsey\Uuid\Uuid;
use const JSON_THROW_ON_ERROR;

use function json_decode;
use function json_encode;
use function sprintf;

/**
 * @coversNothing
 */
class ExperienceControllerTest extends WebTestCase
{
    public function testExperienceCreationEnforcesUserIdOwnership(): void
    {
        $client = static::createClient();
        $fixtureUserId = ResumeFixtures::USER_ID;

        $client->request(
            method: 'GET',
            uri: sprintf('/api/public/resume/%s', $fixtureUserId),
            server: $this->getJsonHeaders(),
        );
        $this->assertResponseIsSuccessful();
        $profile = json_decode((string)$client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        $resumeId = $profile['resume']['id'];

        $client->request(
            method: 'POST',
            uri: '/api/v1/experience',
            server: $this->getJsonHeaders(),
            content: (string)json_encode([
                'userId' => Uuid::uuid4()->toString(),
                'resumeId' => $resumeId,
                'company' => 'Inconsistent User Co.',
                'role' => 'Imposter',
                'startDate' => '2022-01-01',
                'endDate' => '2023-01-01',
                'isCurrent' => false,
                'position' => 1,
            ])
        );

        $this->assertResponseStatusCodeSame(400);
        $error = (string)$client->getResponse()->getContent();
        self::assertStringContainsString('userId does not match the resume owner', $error);
    }
}
