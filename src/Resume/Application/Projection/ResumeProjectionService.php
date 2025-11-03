<?php

declare(strict_types=1);

namespace App\Resume\Application\Projection;

use App\General\Domain\ValueObject\UserId;
use App\Resume\Application\Resource\EducationResource;
use App\Resume\Application\Resource\ExperienceResource;
use App\Resume\Application\Resource\HobbyResource;
use App\Resume\Application\Resource\LanguageResource;
use App\Resume\Application\Resource\ResumeResource;
use App\Resume\Application\Resource\SkillResource;
use App\Resume\Domain\Entity\Education;
use App\Resume\Domain\Entity\Experience;
use App\Resume\Domain\Entity\Hobby;
use App\Resume\Domain\Entity\Language;
use App\Resume\Domain\Entity\Resume;
use App\Resume\Domain\Entity\Skill;

/**
 * Class ResumeProjectionService
 */
readonly class ResumeProjectionService
{
    public function __construct(
        private ResumeResource $resumeResource,
        private ExperienceResource $experienceResource,
        private EducationResource $educationResource,
        private SkillResource $skillResource,
        private LanguageResource $languageResource,
        private HobbyResource $hobbyResource,
    ) {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getResumeProfile(UserId $userId): ?array
    {
        $resume = $this->resumeResource->findOneByUserId($userId);

        if (!$resume instanceof Resume) {
            return null;
        }

        return [
            'resume' => $this->normalizeResume($resume),
            'experiences' => $this->getExperiences($userId),
            'education' => $this->getEducation($userId),
            'skills' => $this->getSkills($userId),
            'languages' => $this->getLanguages($userId),
            'hobbies' => $this->getHobbies($userId),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getExperiences(UserId $userId): array
    {
        $experiences = $this->experienceResource->findByUserId($userId);

        return array_map(
            fn (Experience $experience): array => $this->normalizeExperience($experience),
            $experiences
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getEducation(UserId $userId): array
    {
        $educations = $this->educationResource->findByUserId($userId);

        return array_map(
            fn (Education $education): array => $this->normalizeEducation($education),
            $educations
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getSkills(UserId $userId): array
    {
        $skills = $this->skillResource->findByUserId($userId);

        return array_map(
            fn (Skill $skill): array => $this->normalizeSkill($skill),
            $skills
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getLanguages(UserId $userId): array
    {
        $languages = $this->languageResource->findByUserId($userId);

        return array_map(
            fn (Language $language): array => $this->normalizeLanguage($language),
            $languages
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getHobbies(UserId $userId): array
    {
        $hobbies = $this->hobbyResource->findByUserId($userId);

        return array_map(
            fn (Hobby $hobby): array => $this->normalizeHobby($hobby),
            $hobbies
        );
    }

    private function normalizeResume(Resume $resume): array
    {
        return [
            'id' => $resume->getId(),
            'userId' => (string)$resume->getUserId(),
            'fullName' => $resume->getFullName(),
            'headline' => $resume->getHeadline(),
            'summary' => $resume->getSummary(),
            'location' => $resume->getLocation(),
            'email' => $resume->getEmail(),
            'phone' => $resume->getPhone(),
            'website' => $resume->getWebsite(),
            'avatarUrl' => $resume->getAvatarUrl(),
            'updatedAt' => $resume->getUpdatedAt()?->format(DATE_ATOM),
            'createdAt' => $resume->getCreatedAt()?->format(DATE_ATOM),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeExperience(Experience $experience): array
    {
        return [
            'id' => $experience->getId(),
            'resumeId' => $experience->getResume()?->getId(),
            'company' => $experience->getCompany(),
            'role' => $experience->getRole(),
            'startDate' => $experience->getStartDate()->format('Y-m-d'),
            'endDate' => $experience->getEndDate()?->format('Y-m-d'),
            'isCurrent' => $experience->isCurrent(),
            'position' => $experience->getPosition(),
            'location' => $experience->getLocation(),
            'description' => $experience->getDescription(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeEducation(Education $education): array
    {
        return [
            'id' => $education->getId(),
            'resumeId' => $education->getResume()?->getId(),
            'school' => $education->getSchool(),
            'degree' => $education->getDegree(),
            'field' => $education->getField(),
            'startDate' => $education->getStartDate()?->format('Y-m-d'),
            'endDate' => $education->getEndDate()?->format('Y-m-d'),
            'isCurrent' => $education->isCurrent(),
            'position' => $education->getPosition(),
            'description' => $education->getDescription(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeSkill(Skill $skill): array
    {
        return [
            'id' => $skill->getId(),
            'resumeId' => $skill->getResume()?->getId(),
            'name' => $skill->getName(),
            'category' => $skill->getCategory(),
            'level' => $skill->getLevel(),
            'position' => $skill->getPosition(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeLanguage(Language $language): array
    {
        return [
            'id' => $language->getId(),
            'resumeId' => $language->getResume()?->getId(),
            'name' => $language->getName(),
            'category' => $language->getCategory(),
            'level' => $language->getLevel(),
            'position' => $language->getPosition(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeHobby(Hobby $hobby): array
    {
        return [
            'id' => $hobby->getId(),
            'resumeId' => $hobby->getResume()?->getId(),
            'name' => $hobby->getName(),
            'category' => $hobby->getCategory(),
            'level' => $hobby->getLevel(),
            'position' => $hobby->getPosition(),
        ];
    }
}
