<?php

declare(strict_types=1);

namespace App\Resume\Infrastructure\DataFixtures;

use App\General\Domain\Rest\UuidHelper;
use App\General\Domain\ValueObject\UserId;
use App\Resume\Domain\Entity\Education;
use App\Resume\Domain\Entity\Experience;
use App\Resume\Domain\Entity\Hobby;
use App\Resume\Domain\Entity\Language;
use App\Resume\Domain\Entity\Project;
use App\Resume\Domain\Entity\Resume;
use App\Resume\Domain\Entity\Skill;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Override;

class ResumeFixtures extends Fixture
{
    public const string USER_ID = '20000000-0000-1000-8000-000000000001';

    #[Override]
    public function load(ObjectManager $manager): void
    {
        $user = UuidHelper::fromString(self::USER_ID);
        $userId = new UserId($user->toString());

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
            ->setCompanyLocation('Montréal, QC')
            ->setCompanyLogo('https://cdn.example.com/bro-world/logo.png')
            ->setRole('Founder & Principal Engineer')
            ->setStartDate(new DateTimeImmutable('2019-01-01'))
            ->setIsCurrent(true)
            ->setPosition(0)
            ->setLocation('Remote')
            ->setDescription('Leading product, code and community initiatives across the Bro ecosystem.');

        $experience2 = (new Experience())
            ->setUserId($userId)
            ->setCompany('Indie Labs')
            ->setCompanyLocation('Paris, France')
            ->setCompanyLogo('https://cdn.example.com/indie-labs/logo.png')
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
            ->setSchoolLocation('Montréal, QC')
            ->setSchoolLogo('https://cdn.example.com/bro-tech/logo.png')
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

        $project1 = (new Project())
            ->setUserId($userId)
            ->setTitle('Resume Platform')
            ->setDescription('Composable resume builder empowering builders worldwide.')
            ->setLogoUrl('https://cdn.example.com/projects/resume-platform.png')
            ->setUrlDemo('https://resume.bro.dev')
            ->setUrlRepository('https://github.com/bro-world/resume-platform')
            ->setStatus(Project::STATUS_PUBLIC)
            ->setPosition(0);

        $project2 = (new Project())
            ->setUserId($userId)
            ->setTitle('Bro CLI')
            ->setDescription('Developer-first toolkit for shipping indie products.')
            ->setLogoUrl('https://cdn.example.com/projects/bro-cli.png')
            ->setUrlDemo('https://cli.bro.dev')
            ->setUrlRepository('https://github.com/bro-world/bro-cli')
            ->setStatus(Project::STATUS_PRIVATE)
            ->setPosition(1);

        $language1 = (new Language())
            ->setUserId($userId)
            ->setName('English')
            ->setLevel('native')
            ->setCategory('Spoken')
            ->setPosition(0);

        $language2 = (new Language())
            ->setUserId($userId)
            ->setName('French')
            ->setLevel('fluent')
            ->setCategory('Spoken')
            ->setPosition(1);

        $hobby1 = (new Hobby())
            ->setUserId($userId)
            ->setName('Indie game design')
            ->setLevel('enthusiast')
            ->setCategory('Creative')
            ->setPosition(0);

        $hobby2 = (new Hobby())
            ->setUserId($userId)
            ->setName('Trail running')
            ->setLevel('advanced')
            ->setCategory('Outdoors')
            ->setPosition(1);

        $resume
            ->addExperience($experience1)
            ->addExperience($experience2)
            ->addEducation($education)
            ->addSkill($skill1)
            ->addSkill($skill2)
            ->addSkill($skill3)
            ->addProject($project1)
            ->addProject($project2)
            ->addLanguage($language1)
            ->addLanguage($language2)
            ->addHobby($hobby1)
            ->addHobby($hobby2);

        $manager->persist($resume);
        $manager->flush();
    }
}
