<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20250305093000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Add project entity and extend company/school metadata';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Migration can only be executed safely on "mysql".'
        );

        $this->addSql('ALTER TABLE resume_experience ADD company_location VARCHAR(255) DEFAULT NULL, ADD company_logo VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE resume_education ADD school_location VARCHAR(255) DEFAULT NULL, ADD school_logo VARCHAR(255) DEFAULT NULL');

        $this->addSql(<<<SQL
CREATE TABLE resume_project (
    id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid_binary_ordered_time)',
    resume_id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid_binary_ordered_time)',
    user_id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid_binary_ordered_time)',
    title VARCHAR(255) NOT NULL,
    description LONGTEXT DEFAULT NULL,
    logo_url VARCHAR(255) DEFAULT NULL,
    url_demo VARCHAR(255) DEFAULT NULL,
    url_repository VARCHAR(255) DEFAULT NULL,
    status VARCHAR(20) NOT NULL,
    position INT NOT NULL,
    created_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
    updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
    INDEX IDX_RESUME_PROJECT_RESUME (resume_id),
    INDEX IDX_RESUME_PROJECT_USER (user_id),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
SQL);

        $this->addSql('ALTER TABLE resume_project ADD CONSTRAINT FK_RESUME_PROJECT_RESUME FOREIGN KEY (resume_id) REFERENCES resume (id) ON DELETE CASCADE');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Migration can only be executed safely on "mysql".'
        );

        $this->addSql('ALTER TABLE resume_project DROP FOREIGN KEY FK_RESUME_PROJECT_RESUME');
        $this->addSql('DROP TABLE resume_project');
        $this->addSql('ALTER TABLE resume_experience DROP company_location, DROP company_logo');
        $this->addSql('ALTER TABLE resume_education DROP school_location, DROP school_logo');
    }
}
