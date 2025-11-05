<?php

declare(strict_types=1);

namespace App\Tests\Integration\Resume\Application\Projection;

use Bro\WorldCoreBundle\Domain\ValueObject\UserId;
use App\Resume\Application\Projection\ResumeProjectionService;
use App\Resume\Infrastructure\DataFixtures\ResumeFixtures;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @covers \App\Resume\Application\Projection\ResumeProjectionService
 */
class ResumeProjectionServiceTest extends KernelTestCase
{
    public function testProjectionAggregatesResumeEducationExperienceSkillsLanguagesAndHobbies(): void
    {
        self::bootKernel();
        $service = static::getContainer()->get(ResumeProjectionService::class);
        $payload = $service->getResumeProfile(new UserId(ResumeFixtures::USER_ID));

        self::assertIsArray($payload);
        self::assertArrayHasKey('resume', $payload);
        self::assertArrayHasKey('experiences', $payload);
        self::assertArrayHasKey('education', $payload);
        self::assertArrayHasKey('skills', $payload);
        self::assertArrayHasKey('languages', $payload);
        self::assertArrayHasKey('hobbies', $payload);
        self::assertSame(ResumeFixtures::USER_ID, $payload['resume']['userId']);
        self::assertNotEmpty($payload['experiences']);
        self::assertNotEmpty($payload['education']);
        self::assertNotEmpty($payload['skills']);
        self::assertNotEmpty($payload['languages']);
        self::assertNotEmpty($payload['hobbies']);
    }
}
