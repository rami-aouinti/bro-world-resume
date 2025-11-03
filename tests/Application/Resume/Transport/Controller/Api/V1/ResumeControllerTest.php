<?php

declare(strict_types=1);

namespace App\Tests\Application\Resume\Transport\Controller\Api\V1;

use App\General\Infrastructure\Service\LexikJwtAuthenticatorService;
use App\Tests\TestCase\WebTestCase;
use Ramsey\Uuid\Uuid;

use function json_decode;
use function json_encode;
use function sprintf;

use const JSON_THROW_ON_ERROR;

/**
 * @coversNothing
 */
class ResumeControllerTest extends WebTestCase
{
    public function testResumeLifecycleThroughHttpEndpoints(): void
    {
        $client = static::createClient();
        $userId = Uuid::uuid4()->toString();
        $authHeaders = $this->getAuthHeaders($userId);

        $client->request(
            method: 'POST',
            uri: '/api/v1/resume',
            server: $authHeaders,
            content: (string)json_encode([
                'fullName' => 'Casey Resume',
                'headline' => 'Product-minded engineer',
                'summary' => 'Shaping delightful developer experiences.',
                'location' => 'Remote',
                'email' => 'casey@example.com',
                'phone' => '+1 555 0101 010',
                'website' => 'https://casey.dev',
            ])
        );

        $this->assertResponseStatusCodeSame(201);
        $resume = json_decode((string)$client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertIsArray($resume);
        self::assertArrayHasKey('id', $resume);
        self::assertSame($userId, $resume['userId']);
        $resumeId = $resume['id'];

        $client->request(
            method: 'POST',
            uri: '/api/v1/experience',
            server: $authHeaders,
            content: (string)json_encode([
                'resumeId' => $resumeId,
                'company' => 'Bro World Studios',
                'role' => 'Principal Engineer',
                'startDate' => '2020-01-01',
                'endDate' => null,
                'isCurrent' => true,
                'position' => 0,
                'location' => 'Remote',
                'description' => 'Leading the resume platform evolution.',
            ])
        );
        $this->assertResponseStatusCodeSame(201);

        $client->request(
            method: 'POST',
            uri: '/api/v1/education',
            server: $authHeaders,
            content: (string)json_encode([
                'resumeId' => $resumeId,
                'school' => 'Bro World Academy',
                'degree' => 'MSc Software Engineering',
                'field' => 'Platform Design',
                'startDate' => '2015-09-01',
                'endDate' => '2017-06-01',
                'isCurrent' => false,
                'position' => 0,
                'description' => 'Research on low-latency resume projections.',
            ])
        );
        $this->assertResponseStatusCodeSame(201);

        $client->request(
            method: 'POST',
            uri: '/api/v1/skill',
            server: $authHeaders,
            content: (string)json_encode([
                'resumeId' => $resumeId,
                'name' => 'Symfony',
                'category' => 'Backend',
                'level' => 'expert',
                'position' => 0,
            ])
        );
        $this->assertResponseStatusCodeSame(201);

        $client->request(
            method: 'PATCH',
            uri: sprintf('/api/v1/resume/%s', $resumeId),
            server: $authHeaders,
            content: (string)json_encode([
                'headline' => 'Principal engineer & resume curator',
                'summary' => 'Curating dependable resume experiences.',
            ])
        );
        $this->assertResponseIsSuccessful();
        $updatedResume = json_decode((string)$client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('Principal engineer & resume curator', $updatedResume['headline']);
        self::assertSame('Curating dependable resume experiences.', $updatedResume['summary']);

        $client->request(
            method: 'GET',
            uri: sprintf('/api/v1/resume/%s', $resumeId),
            server: $authHeaders,
        );
        $this->assertResponseIsSuccessful();
        $fetched = json_decode((string)$client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame($resumeId, $fetched['id']);
        self::assertSame($userId, $fetched['userId']);

        $client->request(
            method: 'GET',
            uri: sprintf('/api/public/resume/%s', $userId),
            server: $this->getJsonHeaders(),
        );
        $this->assertResponseIsSuccessful();
        $profile = json_decode((string)$client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('resume', $profile);
        self::assertArrayHasKey('experiences', $profile);
        self::assertArrayHasKey('education', $profile);
        self::assertArrayHasKey('skills', $profile);
        self::assertSame('Principal engineer & resume curator', $profile['resume']['headline']);
        self::assertCount(1, $profile['experiences']);
        self::assertCount(1, $profile['education']);
        self::assertCount(1, $profile['skills']);
        self::assertSame('Bro World Studios', $profile['experiences'][0]['company']);
        self::assertTrue($profile['experiences'][0]['isCurrent']);
        self::assertSame('Symfony', $profile['skills'][0]['name']);
    }

    /**
     * @return array<string, string>
     */
    private function getAuthHeaders(string $userId): array
    {
        self::bootKernel();
        $tokenService = self::getContainer()->get(LexikJwtAuthenticatorService::class);
        $token = $tokenService->getToken($userId);
        self::ensureKernelShutdown();

        return $this->getJsonHeaders() + [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
        ];
    }
}
