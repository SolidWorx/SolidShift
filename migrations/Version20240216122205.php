<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240216122205 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change shift start time to nullable';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shift CHANGE schedule_id schedule_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:ulid)\', CHANGE start_time start_time TIME DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shift CHANGE schedule_id schedule_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE start_time start_time TIME NOT NULL COMMENT \'(DC2Type:time_immutable)\'');
    }
}
