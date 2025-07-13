<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250713065243 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B7E704F05CA2E8E5 ON notification_channel (secret)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_B7E704F05CA2E8E5');
    }
}
