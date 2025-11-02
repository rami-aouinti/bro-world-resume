<?php

declare(strict_types=1);

namespace App\Resume\Application\Projection;

use App\General\Domain\ValueObject\UserId;
use App\Resume\Application\Resource\EducationResource;
use App\Resume\Application\Resource\ExperienceResource;
use App\Resume\Application\Resource\ResumeResource;
use App\Resume\Application\Resource\SkillResource;
use App\Resume\Domain\Entity\Education;
use App\Resume\Domain\Entity\Experience;
use App\Resume\Domain\Entity\Resume;
use App\Resume\Domain\Entity\Skill;

class ResumeProjectionService
{
    public function __construct(
        private readonly ResumeResource $resumeResource,
        private readonly ExperienceResource $experienceResource,
        private readonly EducationResource $educationResource,
        private readonly SkillResource $skillResource,
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

        $experiences = $this->experienceResource->findByUserId($userId);
        $educations = $this->educationResource->findByUserId($userId);
        $skills = $this->skillResource->findByUserId($userId);

        return [
            'resume' => $this->normalizeResume($resume),
            'experiences' => array_map(fn (Experience $experience): array => $this->normalizeExperience($experience), $experiences),
            'education' => array_map(fn (Education $education): array => $this->normalizeEducation($education), $educations),
            'skills' => array_map(fn (Skill $skill): array => $this->normalizeSkill($skill), $skills),
        ];
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
}
