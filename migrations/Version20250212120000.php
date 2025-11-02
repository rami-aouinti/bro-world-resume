<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20250212120000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Add resume, experience, education and skill tables';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Migration can only be executed safely on \"mysql\".'
        );

        $this->addSql(<<<SQL
CREATE TABLE resume (
    id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid_binary_ordered_time)',
    user_id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid_binary_ordered_time)',
    full_name VARCHAR(255) NOT NULL,
    headline VARCHAR(255) NOT NULL,
    location VARCHAR(255) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    phone VARCHAR(64) DEFAULT NULL,
    website VARCHAR(255) DEFAULT NULL,
    avatar_url VARCHAR(255) DEFAULT NULL,
    summary LONGTEXT DEFAULT NULL,
    created_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
    updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
    UNIQUE INDEX UNIQ_RESUME_USER (user_id),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
SQL);

        $this->addSql(<<<SQL
CREATE TABLE resume_experience (
    id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid_binary_ordered_time)',
    resume_id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid_binary_ordered_time)',
    user_id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid_binary_ordered_time)',
    company VARCHAR(255) NOT NULL,
    role VARCHAR(255) NOT NULL,
    start_date DATE NOT NULL COMMENT '(DC2Type:date_immutable)',
    end_date DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
    is_current TINYINT(1) NOT NULL,
    position INT NOT NULL,
    location VARCHAR(255) DEFAULT NULL,
    description LONGTEXT DEFAULT NULL,
    created_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
    updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
    INDEX IDX_RESUME_EXPERIENCE_RESUME (resume_id),
    INDEX IDX_RESUME_EXPERIENCE_USER (user_id),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
SQL);

        $this->addSql(<<<SQL
CREATE TABLE resume_education (
    id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid_binary_ordered_time)',
    resume_id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid_binary_ordered_time)',
    user_id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid_binary_ordered_time)',
    school VARCHAR(255) NOT NULL,
    degree VARCHAR(255) DEFAULT NULL,
    field VARCHAR(255) DEFAULT NULL,
    start_date DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
    end_date DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)',
    is_current TINYINT(1) NOT NULL,
    position INT NOT NULL,
    description LONGTEXT DEFAULT NULL,
    created_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
    updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
    INDEX IDX_RESUME_EDUCATION_RESUME (resume_id),
    INDEX IDX_RESUME_EDUCATION_USER (user_id),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
SQL);

        $this->addSql(<<<SQL
CREATE TABLE resume_skill (
    id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid_binary_ordered_time)',
    resume_id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid_binary_ordered_time)',
    user_id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid_binary_ordered_time)',
    name VARCHAR(255) NOT NULL,
    category VARCHAR(255) DEFAULT NULL,
    level VARCHAR(50) DEFAULT NULL,
    position INT NOT NULL,
    created_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
    updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
    INDEX IDX_RESUME_SKILL_RESUME (resume_id),
    INDEX IDX_RESUME_SKILL_USER (user_id),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
SQL);

        $this->addSql('ALTER TABLE resume_experience ADD CONSTRAINT FK_RESUME_EXPERIENCE_RESUME FOREIGN KEY (resume_id) REFERENCES resume (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resume_education ADD CONSTRAINT FK_RESUME_EDUCATION_RESUME FOREIGN KEY (resume_id) REFERENCES resume (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resume_skill ADD CONSTRAINT FK_RESUME_SKILL_RESUME FOREIGN KEY (resume_id) REFERENCES resume (id) ON DELETE CASCADE');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Migration can only be executed safely on \"mysql\".'
        );

        $this->addSql('ALTER TABLE resume_experience DROP FOREIGN KEY FK_RESUME_EXPERIENCE_RESUME');
        $this->addSql('ALTER TABLE resume_education DROP FOREIGN KEY FK_RESUME_EDUCATION_RESUME');
        $this->addSql('ALTER TABLE resume_skill DROP FOREIGN KEY FK_RESUME_SKILL_RESUME');
        $this->addSql('DROP TABLE resume_skill');
        $this->addSql('DROP TABLE resume_education');
        $this->addSql('DROP TABLE resume_experience');
        $this->addSql('DROP TABLE resume');
    }
}
