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
        self::assertArrayHasKey('languages', $data);
        self::assertArrayHasKey('hobbies', $data);
        self::assertSame('Alex "Bro" Devaux', $data['resume']['fullName']);
        self::assertIsArray($data['experiences']);
        self::assertIsArray($data['education']);
        self::assertIsArray($data['skills']);
        self::assertIsArray($data['languages']);
        self::assertIsArray($data['hobbies']);
        self::assertNotEmpty($data['experiences']);
        self::assertNotEmpty($data['education']);
        self::assertNotEmpty($data['skills']);
        self::assertNotEmpty($data['languages']);
        self::assertNotEmpty($data['hobbies']);
        self::assertIsArray($data['experiences'][0]);
        self::assertIsArray($data['education'][0]);
        self::assertIsArray($data['skills'][0]);
        self::assertIsArray($data['languages'][0]);
        self::assertIsArray($data['hobbies'][0]);
        self::assertCount(2, $data['experiences']);
        self::assertCount(1, $data['education']);
        self::assertCount(3, $data['skills']);
        self::assertCount(2, $data['languages']);
        self::assertCount(2, $data['hobbies']);
    }

    public function testPublicResumeProfileNotFound(): void
    {
        $this->client->request('GET', self::API_URL_PREFIX . '/public/resume/' . Uuid::v4()->toRfc4122());

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testPublicResumeExperiencesEndpoint(): void
    {
        $this->client->request('GET', self::API_URL_PREFIX . '/public/resume/' . ResumeFixtures::USER_ID . '/experiences');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = json_decode($this->client->getResponse()->getContent() ?: '', true, 512, JSON_THROW_ON_ERROR);

        self::assertIsArray($data);
        self::assertCount(2, $data);
        self::assertIsArray($data[0]);
        self::assertSame('Bro World Studios', $data[0]['company']);
    }

    public function testPublicResumeEducationEndpoint(): void
    {
        $this->client->request('GET', self::API_URL_PREFIX . '/public/resume/' . ResumeFixtures::USER_ID . '/education');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = json_decode($this->client->getResponse()->getContent() ?: '', true, 512, JSON_THROW_ON_ERROR);

        self::assertIsArray($data);
        self::assertCount(1, $data);
        self::assertIsArray($data[0]);
        self::assertSame('MSc Software Engineering', $data[0]['degree']);
    }

    public function testPublicResumeSkillsEndpoint(): void
    {
        $this->client->request('GET', self::API_URL_PREFIX . '/public/resume/' . ResumeFixtures::USER_ID . '/skills');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = json_decode($this->client->getResponse()->getContent() ?: '', true, 512, JSON_THROW_ON_ERROR);

        self::assertIsArray($data);
        self::assertCount(3, $data);
        self::assertIsArray($data[0]);
        self::assertSame('Symfony', $data[0]['name']);
    }

    public function testPublicResumeLanguagesEndpoint(): void
    {
        $this->client->request('GET', self::API_URL_PREFIX . '/public/resume/' . ResumeFixtures::USER_ID . '/languages');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = json_decode($this->client->getResponse()->getContent() ?: '', true, 512, JSON_THROW_ON_ERROR);

        self::assertIsArray($data);
        self::assertCount(2, $data);
        self::assertIsArray($data[0]);
        self::assertSame('English', $data[0]['name']);
    }

    public function testPublicResumeHobbiesEndpoint(): void
    {
        $this->client->request('GET', self::API_URL_PREFIX . '/public/resume/' . ResumeFixtures::USER_ID . '/hobbies');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $data = json_decode($this->client->getResponse()->getContent() ?: '', true, 512, JSON_THROW_ON_ERROR);

        self::assertIsArray($data);
        self::assertCount(2, $data);
        self::assertIsArray($data[0]);
        self::assertSame('Indie game design', $data[0]['name']);
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

    public function testPlatformResumeCreateEndpointPersistsResume(): void
    {
        $userId = Uuid::v4()->toRfc4122();
        $payload = [
            'fullName' => 'Morgan Strategy',
            'headline' => 'Strategist and operations lead',
            'summary' => 'Blending vision with execution to accelerate growth.',
            'location' => 'Remote',
            'email' => 'morgan@example.com',
        ];

        $this->client->request(
            'POST',
            self::API_URL_PREFIX . '/platform/resume/create',
            [],
            [],
            $this->getAuthorizedHeaders($userId),
            json_encode($payload, JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $data = json_decode($this->client->getResponse()->getContent() ?: '', true, 512, JSON_THROW_ON_ERROR);

        self::assertSame($payload['fullName'], $data['fullName']);
        self::assertSame($payload['headline'], $data['headline']);
        self::assertSame($userId, $data['userId']);

        static::bootKernel();
        $container = static::getContainer();
        /** @var ResumeRepositoryInterface $resumeRepository */
        $resumeRepository = $container->get(ResumeRepositoryInterface::class);
        $resume = $resumeRepository->findOneByUserId(new UserId($userId));
        static::ensureKernelShutdown();

        self::assertNotNull($resume);
        self::assertSame($payload['fullName'], $resume?->getFullName());
        self::assertSame($payload['headline'], $resume?->getHeadline());
        self::assertSame($payload['summary'], $resume?->getSummary());
        self::assertSame($payload['location'], $resume?->getLocation());
        self::assertSame($payload['email'], $resume?->getEmail());
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

    public function testLanguageLifecycle(): void
    {
        $createPayload = [
            'resumeId' => $this->resumeId,
            'name' => 'Spanish',
            'category' => 'Spoken',
            'level' => 'basic',
            'position' => 5,
        ];

        $this->client->request(
            'POST',
            self::API_URL_PREFIX . '/v1/language',
            [],
            [],
            $this->getAuthorizedHeaders(ResumeFixtures::USER_ID),
            json_encode($createPayload, JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $language = json_decode($this->client->getResponse()->getContent() ?: '', true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('id', $language);

        $languageId = $language['id'];

        $this->client->request(
            'PATCH',
            self::API_URL_PREFIX . '/v1/language/' . $languageId,
            [],
            [],
            $this->getAuthorizedHeaders(ResumeFixtures::USER_ID),
            json_encode(['level' => 'conversational'], JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->client->request(
            'GET',
            self::API_URL_PREFIX . '/v1/language/' . $languageId,
            [],
            [],
            $this->getAuthorizedHeaders(ResumeFixtures::USER_ID)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $language = json_decode($this->client->getResponse()->getContent() ?: '', true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('conversational', $language['level']);

        $this->client->request('GET', self::API_URL_PREFIX . '/public/resume/' . ResumeFixtures::USER_ID . '/languages');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $languages = json_decode($this->client->getResponse()->getContent() ?: '', true, 512, JSON_THROW_ON_ERROR);
        self::assertCount(3, $languages);

        $this->client->request(
            'DELETE',
            self::API_URL_PREFIX . '/v1/language/' . $languageId,
            [],
            [],
            $this->getAuthorizedHeaders(ResumeFixtures::USER_ID)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $this->client->request('GET', self::API_URL_PREFIX . '/public/resume/' . ResumeFixtures::USER_ID . '/languages');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $languages = json_decode($this->client->getResponse()->getContent() ?: '', true, 512, JSON_THROW_ON_ERROR);
        self::assertCount(2, $languages);

        $this->client->request(
            'GET',
            self::API_URL_PREFIX . '/v1/language/' . $languageId,
            [],
            [],
            $this->getAuthorizedHeaders(ResumeFixtures::USER_ID)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testHobbyLifecycle(): void
    {
        $createPayload = [
            'resumeId' => $this->resumeId,
            'name' => 'Photography',
            'category' => 'Creative',
            'level' => 'intermediate',
            'position' => 4,
        ];

        $this->client->request(
            'POST',
            self::API_URL_PREFIX . '/v1/hobby',
            [],
            [],
            $this->getAuthorizedHeaders(ResumeFixtures::USER_ID),
            json_encode($createPayload, JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $hobby = json_decode($this->client->getResponse()->getContent() ?: '', true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('id', $hobby);

        $hobbyId = $hobby['id'];

        $this->client->request(
            'PUT',
            self::API_URL_PREFIX . '/v1/hobby/' . $hobbyId,
            [],
            [],
            $this->getAuthorizedHeaders(ResumeFixtures::USER_ID),
            json_encode([
                'resumeId' => $this->resumeId,
                'name' => 'Photography',
                'category' => 'Creative',
                'level' => 'advanced',
                'position' => 4,
            ], JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->client->request(
            'GET',
            self::API_URL_PREFIX . '/v1/hobby/' . $hobbyId,
            [],
            [],
            $this->getAuthorizedHeaders(ResumeFixtures::USER_ID)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $hobby = json_decode($this->client->getResponse()->getContent() ?: '', true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('advanced', $hobby['level']);

        $this->client->request('GET', self::API_URL_PREFIX . '/public/resume/' . ResumeFixtures::USER_ID . '/hobbies');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $hobbies = json_decode($this->client->getResponse()->getContent() ?: '', true, 512, JSON_THROW_ON_ERROR);
        self::assertCount(3, $hobbies);

        $this->client->request(
            'DELETE',
            self::API_URL_PREFIX . '/v1/hobby/' . $hobbyId,
            [],
            [],
            $this->getAuthorizedHeaders(ResumeFixtures::USER_ID)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $this->client->request('GET', self::API_URL_PREFIX . '/public/resume/' . ResumeFixtures::USER_ID . '/hobbies');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $hobbies = json_decode($this->client->getResponse()->getContent() ?: '', true, 512, JSON_THROW_ON_ERROR);
        self::assertCount(2, $hobbies);

        $this->client->request(
            'GET',
            self::API_URL_PREFIX . '/v1/hobby/' . $hobbyId,
            [],
            [],
            $this->getAuthorizedHeaders(ResumeFixtures::USER_ID)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
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
