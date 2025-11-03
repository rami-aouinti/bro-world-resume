<?php

declare(strict_types=1);

namespace App\Resume\Application\Projection;

use App\Resume\Domain\Entity\Education;
use App\Resume\Domain\Entity\Experience;
use App\Resume\Domain\Entity\Hobby;
use App\Resume\Domain\Entity\Language;
use App\Resume\Domain\Entity\Resume;
use App\Resume\Domain\Entity\Skill;

trait ResumeEntryNormalizerTrait
{
    /**
     * @return array<string, mixed>
     */
    protected function normalizeResume(Resume $resume): array
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
    protected function normalizeExperience(Experience $experience): array
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
    protected function normalizeEducation(Education $education): array
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
    protected function normalizeSkill(Skill $skill): array
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
    protected function normalizeLanguage(Language $language): array
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
    protected function normalizeHobby(Hobby $hobby): array
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
