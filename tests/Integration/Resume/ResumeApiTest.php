<?php

declare(strict_types=1);

namespace App\Tests\Integration\Resume;

use App\General\Domain\ValueObject\UserId;
use App\General\Infrastructure\Service\LexikJwtAuthenticatorService;
use App\Resume\Infrastructure\DataFixtures\ResumeFixtures;
use App\Resume\Domain\Repository\ResumeRepositoryInterface;
use App\Tests\TestCase\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;
use function sprintf;

class ResumeApiTest extends WebTestCase
{
    private KernelBrowser $client;

    private string $resumeId;

    protected function setUp(): void
    {
        parent::setUp();

        static::bootKernel();
        $container = static::getContainer();

        $entityManager = $container->get(EntityManagerInterface::class);
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropDatabase();
        $schemaTool->createSchema($metadata);

        /** @var ResumeFixtures $fixtures */
        $fixtures = $container->get(ResumeFixtures::class);
        $fixtures->load($entityManager);

        /** @var ResumeRepositoryInterface $resumeRepository */
        $resumeRepository = $container->get(ResumeRepositoryInterface::class);
        $resume = $resumeRepository->findOneByUserId(new UserId(ResumeFixtures::USER_ID));
        $this->resumeId = $resume?->getId() ?? '';

        self::ensureKernelShutdown();
        $this->client = static::createClient([], $this->getJsonHeaders());
    }

    public function testPublicResumeProfileEndpoint(): void
    {
        $this->client->request('GET', self::API_URL_PREFIX . '/public/resume/' . ResumeFixtures::USER_ID);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = json_decode($this->client->getResponse()->getContent() ?: '', true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('resume', $data);
        self::assertArrayHasKey('experiences', $data);
        self::assertArrayHasKey('education', $data);
        self::assertArrayHasKey('skills', $data);
        self::assertSame('Alex "Bro" Devaux', $data['resume']['fullName']);
        self::assertCount(2, $data['experiences']);
        self::assertCount(1, $data['education']);
        self::assertCount(3, $data['skills']);
    }

    public function testPublicResumeProfileNotFound(): void
    {
        $this->client->request('GET', self::API_URL_PREFIX . '/public/resume/' . Uuid::v4()->toRfc4122());

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testCanCreateResumeViaApi(): void
    {
        $userId = Uuid::v4()->toRfc4122();
        $payload = [
            'fullName' => 'Jamie Portfolio',
            'headline' => 'Product designer & motion tinkerer',
            'summary' => 'Designing intuitive journeys with a fondness for delightful micro-interactions.',
            'location' => 'Lisbon, PT',
            'email' => 'jamie@example.com',
            'phone' => '+351 555 011',
            'website' => 'https://jamie.example',
            'avatarUrl' => 'https://cdn.example.com/jamie.png',
        ];

        $this->client->request(
            'POST',
            self::API_URL_PREFIX . '/v1/resume',
            [],
            [],
            $this->getAuthorizedHeaders($userId),
            json_encode($payload, JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $data = json_decode($this->client->getResponse()->getContent() ?: '', true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('id', $data);
        self::assertSame($payload['fullName'], $data['fullName']);
    }

    public function testCanAppendExperienceToResume(): void
    {
        $payload = [
            'resumeId' => $this->resumeId,
            'company' => 'Bro Ventures',
            'role' => 'Advisory Engineer',
            'startDate' => '2021-05-01',
            'isCurrent' => true,
            'position' => 2,
            'location' => 'Remote',
            'description' => 'Guiding new squads on developer experience best practices.',
        ];

        $this->client->request(
            'POST',
            self::API_URL_PREFIX . '/v1/experience',
            [],
            [],
            $this->getAuthorizedHeaders(ResumeFixtures::USER_ID),
            json_encode($payload, JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $this->client->request('GET', self::API_URL_PREFIX . '/public/resume/' . ResumeFixtures::USER_ID . '/experiences');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $experiences = json_decode($this->client->getResponse()->getContent() ?: '', true, 512, JSON_THROW_ON_ERROR);
        self::assertCount(3, $experiences);
    }

    /**
     * @return array<string, string>
     */
    private function getAuthorizedHeaders(string $userId): array
    {
        static::bootKernel();
        $tokenService = static::getContainer()->get(LexikJwtAuthenticatorService::class);
        $token = $tokenService->getToken($userId);
        static::ensureKernelShutdown();

        return $this->getJsonHeaders() + [
            'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
        ];
    }
}
