<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20250301000000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Add resume_language and resume_hobby tables';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Migration can only be executed safely on "mysql".'
        );

        $this->addSql(<<<SQL
CREATE TABLE resume_language (
    id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid_binary_ordered_time)',
    resume_id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid_binary_ordered_time)',
    user_id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid_binary_ordered_time)',
    name VARCHAR(255) NOT NULL,
    category VARCHAR(255) DEFAULT NULL,
    level VARCHAR(50) DEFAULT NULL,
    position INT NOT NULL,
    created_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
    updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
    INDEX IDX_RESUME_LANGUAGE_RESUME (resume_id),
    INDEX IDX_RESUME_LANGUAGE_USER (user_id),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
SQL);

        $this->addSql(<<<SQL
CREATE TABLE resume_hobby (
    id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid_binary_ordered_time)',
    resume_id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid_binary_ordered_time)',
    user_id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid_binary_ordered_time)',
    name VARCHAR(255) NOT NULL,
    category VARCHAR(255) DEFAULT NULL,
    level VARCHAR(50) DEFAULT NULL,
    position INT NOT NULL,
    created_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
    updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
    INDEX IDX_RESUME_HOBBY_RESUME (resume_id),
    INDEX IDX_RESUME_HOBBY_USER (user_id),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
SQL);

        $this->addSql('ALTER TABLE resume_language ADD CONSTRAINT FK_RESUME_LANGUAGE_RESUME FOREIGN KEY (resume_id) REFERENCES resume (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resume_hobby ADD CONSTRAINT FK_RESUME_HOBBY_RESUME FOREIGN KEY (resume_id) REFERENCES resume (id) ON DELETE CASCADE');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Migration can only be executed safely on "mysql".'
        );

        $this->addSql('ALTER TABLE resume_language DROP FOREIGN KEY FK_RESUME_LANGUAGE_RESUME');
        $this->addSql('ALTER TABLE resume_hobby DROP FOREIGN KEY FK_RESUME_HOBBY_RESUME');
        $this->addSql('DROP TABLE resume_hobby');
        $this->addSql('DROP TABLE resume_language');
    }
}
