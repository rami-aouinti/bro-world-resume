<?php

declare(strict_types=1);

// phpcs:ignoreFile
namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20240901000000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Register scheduled commands for resume cache refresh.';
    }

    #[Override]
    public function isTransactional(): bool
    {
        return false;
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
INSERT INTO scheduled_command (name, command, arguments, cron_expression, priority, execute_immediately, disabled, locked, ping_back_url, ping_back_failed_url, notes, version, created_at)
VALUES
    ('resume_cache_refresh_profile', 'resume:cache:refresh', '--scope=profile', '*/10 * * * *', 0, 0, 0, 0, NULL, NULL, 'Refreshes cached resume projections for each userId.', 1, NOW()),
    ('resume_cache_refresh_public', 'resume:cache:refresh', '--scope=public', '0 * * * *', 0, 0, 0, 0, NULL, NULL, 'Refreshes the aggregated public portfolio payloads.', 1, NOW())
ON DUPLICATE KEY UPDATE
    command = VALUES(command),
    arguments = VALUES(arguments),
    cron_expression = VALUES(cron_expression),
    priority = VALUES(priority),
    execute_immediately = VALUES(execute_immediately),
    disabled = VALUES(disabled),
    locked = VALUES(locked),
    notes = VALUES(notes)
SQL
        );
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
DELETE FROM scheduled_command
WHERE name IN ('resume_cache_refresh_profile', 'resume_cache_refresh_public')
SQL
        );
    }
}
