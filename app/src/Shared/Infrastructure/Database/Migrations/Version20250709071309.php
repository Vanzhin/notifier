<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250709071309 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE notification_channel (data JSONB DEFAULT \'[]\' NOT NULL, type VARCHAR(50) NOT NULL, is_verified BOOLEAN NOT NULL, id UUID NOT NULL, subscription_id UUID DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B7E704F09A1887DC ON notification_channel (subscription_id)');
        $this->addSql('CREATE INDEX channel_data_idx ON notification_channel (data)');
        $this->addSql('CREATE TABLE notification_phone_number (id UUID NOT NULL, PRIMARY KEY(id), phone VARCHAR(17) NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6A0F566E444F97DD ON notification_phone_number (phone)');
        $this->addSql('CREATE TABLE notification_subscription (subscriber_id VARCHAR(36) NOT NULL, events JSONB DEFAULT \'[]\' NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, id UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX subscription_subscriber_idx ON notification_subscription (subscriber_id)');
        $this->addSql('CREATE INDEX subscription_events_idx ON notification_subscription (events)');
        $this->addSql('CREATE TABLE notification_subscription_phone_number (subscription_id UUID NOT NULL, phone_number_id UUID NOT NULL, PRIMARY KEY(subscription_id, phone_number_id))');
        $this->addSql('CREATE INDEX IDX_1755419A1887DC ON notification_subscription_phone_number (subscription_id)');
        $this->addSql('CREATE INDEX IDX_17554139DFD528 ON notification_subscription_phone_number (phone_number_id)');
        $this->addSql('ALTER TABLE notification_channel ADD CONSTRAINT FK_B7E704F09A1887DC FOREIGN KEY (subscription_id) REFERENCES notification_subscription (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification_subscription_phone_number ADD CONSTRAINT FK_1755419A1887DC FOREIGN KEY (subscription_id) REFERENCES notification_subscription (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification_subscription_phone_number ADD CONSTRAINT FK_17554139DFD528 FOREIGN KEY (phone_number_id) REFERENCES notification_phone_number (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification_channel DROP CONSTRAINT FK_B7E704F09A1887DC');
        $this->addSql('ALTER TABLE notification_subscription_phone_number DROP CONSTRAINT FK_1755419A1887DC');
        $this->addSql('ALTER TABLE notification_subscription_phone_number DROP CONSTRAINT FK_17554139DFD528');
        $this->addSql('DROP TABLE notification_channel');
        $this->addSql('DROP TABLE notification_phone_number');
        $this->addSql('DROP TABLE notification_subscription');
        $this->addSql('DROP TABLE notification_subscription_phone_number');
    }
}
