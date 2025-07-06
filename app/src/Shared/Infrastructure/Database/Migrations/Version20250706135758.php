<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250706135758 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE notification_channel (id UUID NOT NULL, type VARCHAR(50) NOT NULL, data JSON DEFAULT \'[]\' NOT NULL, is_verified BOOLEAN NOT NULL, subscription_id UUID DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B7E704F09A1887DC ON notification_channel (subscription_id)');
        $this->addSql('CREATE TABLE notification_subscription (id UUID NOT NULL, phone_number VARCHAR(20) NOT NULL,subscriber_id VARCHAR(36) NOT NULL, events JSON DEFAULT \'[]\' NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,  PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE notification_channel ADD CONSTRAINT FK_B7E704F09A1887DC FOREIGN KEY (subscription_id) REFERENCES notification_subscription (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_type ON notification_channel (type)');
        $this->addSql('CREATE INDEX IDX_phone_number ON notification_subscription (phone_number)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification_channel DROP CONSTRAINT FK_B7E704F09A1887DC');
        $this->addSql('DROP TABLE notification_channel');
        $this->addSql('DROP TABLE notification_subscription');
    }
}
