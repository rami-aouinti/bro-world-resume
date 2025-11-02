<?php

declare(strict_types=1);

namespace App\Resume\Infrastructure\DataFixtures;

use App\General\Domain\ValueObject\UserId;
use App\Resume\Domain\Entity\Education;
use App\Resume\Domain\Entity\Experience;
use App\Resume\Domain\Entity\Resume;
use App\Resume\Domain\Entity\Skill;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Override;

class ResumeFixtures extends Fixture
{
    public const string USER_ID = '4c3a7f10-5d5c-4d1f-9a3a-6b9c2d2d5b10';

    #[Override]
    public function load(ObjectManager $manager): void
    {
        $userId = new UserId(self::USER_ID);

        $resume = (new Resume())
            ->setUserId($userId)
            ->setFullName('Alex "Bro" Devaux')
            ->setHeadline('Full-stack artisan & indie builder')
            ->setSummary('Crafting joyful experiences across web and native platforms, with a dash of ops and a sprinkle of DX love.')
            ->setEmail('hello@bro.dev')
            ->setPhone('+1 555 0100 200')
            ->setWebsite('https://bro.dev')
            ->setAvatarUrl('https://cdn.example.com/bro/avatar.png')
            ->setLocation('Montréal, QC');

        $experience1 = (new Experience())
            ->setUserId($userId)
            ->setCompany('Bro World Studios')
            ->setRole('Founder & Principal Engineer')
            ->setStartDate(new DateTimeImmutable('2019-01-01'))
            ->setIsCurrent(true)
            ->setPosition(0)
            ->setLocation('Remote')
            ->setDescription('Leading product, code and community initiatives across the Bro ecosystem.');

        $experience2 = (new Experience())
            ->setUserId($userId)
            ->setCompany('Indie Labs')
            ->setRole('Senior Software Developer')
            ->setStartDate(new DateTimeImmutable('2015-06-01'))
            ->setEndDate(new DateTimeImmutable('2018-12-31'))
            ->setIsCurrent(false)
            ->setPosition(1)
            ->setLocation('Paris, France')
            ->setDescription('Scaled developer tooling and mentored product squads.');

        $education = (new Education())
            ->setUserId($userId)
            ->setSchool('École Bro de Technologie')
            ->setDegree('MSc Software Engineering')
            ->setStartDate(new DateTimeImmutable('2012-09-01'))
            ->setEndDate(new DateTimeImmutable('2014-06-01'))
            ->setPosition(0)
            ->setDescription('Thesis on resilient cloud-native architectures.');

        $skill1 = (new Skill())
            ->setUserId($userId)
            ->setName('Symfony')
            ->setCategory('Backend')
            ->setLevel('expert')
            ->setPosition(0);

        $skill2 = (new Skill())
            ->setUserId($userId)
            ->setName('Vue.js')
            ->setCategory('Frontend')
            ->setLevel('advanced')
            ->setPosition(1);

        $skill3 = (new Skill())
            ->setUserId($userId)
            ->setName('DevOps')
            ->setCategory('Platform')
            ->setLevel('advanced')
            ->setPosition(2);

        $resume
            ->addExperience($experience1)
            ->addExperience($experience2)
            ->addEducation($education)
            ->addSkill($skill1)
            ->addSkill($skill2)
            ->addSkill($skill3);

        $manager->persist($resume);
        $manager->flush();
    }
}
