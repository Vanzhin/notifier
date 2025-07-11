<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250711055956 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE notification_subscription_channel (subscription_id UUID NOT NULL, channel_id UUID NOT NULL, PRIMARY KEY(subscription_id, channel_id))');
        $this->addSql('CREATE INDEX IDX_BC7B825C9A1887DC ON notification_subscription_channel (subscription_id)');
        $this->addSql('CREATE INDEX IDX_BC7B825C72F5A1AA ON notification_subscription_channel (channel_id)');
        $this->addSql('ALTER TABLE notification_subscription_channel ADD CONSTRAINT FK_BC7B825C9A1887DC FOREIGN KEY (subscription_id) REFERENCES notification_subscription (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification_subscription_channel ADD CONSTRAINT FK_BC7B825C72F5A1AA FOREIGN KEY (channel_id) REFERENCES notification_channel (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification_channel DROP CONSTRAINT fk_b7e704f09a1887dc');
        $this->addSql('DROP INDEX idx_b7e704f09a1887dc');
        $this->addSql('ALTER TABLE notification_channel DROP subscription_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE notification_subscription_channel DROP CONSTRAINT FK_BC7B825C9A1887DC');
        $this->addSql('ALTER TABLE notification_subscription_channel DROP CONSTRAINT FK_BC7B825C72F5A1AA');
        $this->addSql('DROP TABLE notification_subscription_channel');
        $this->addSql('ALTER TABLE notification_channel ADD subscription_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE notification_channel ADD CONSTRAINT fk_b7e704f09a1887dc FOREIGN KEY (subscription_id) REFERENCES notification_subscription (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_b7e704f09a1887dc ON notification_channel (subscription_id)');
    }
}
