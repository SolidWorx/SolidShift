<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250714204050 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE schedule_location DROP FOREIGN KEY FK_E27560FD64D218E');
        $this->addSql('ALTER TABLE schedule_location DROP FOREIGN KEY FK_E27560FDA40BC2D5');
        $this->addSql('DROP TABLE schedule_location');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE schedule_location (schedule_id BINARY(16) NOT NULL, location_id BINARY(16) NOT NULL, INDEX IDX_E27560FDA40BC2D5 (schedule_id), INDEX IDX_E27560FD64D218E (location_id), PRIMARY KEY(schedule_id, location_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE schedule_location ADD CONSTRAINT FK_E27560FD64D218E FOREIGN KEY (location_id) REFERENCES location (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE schedule_location ADD CONSTRAINT FK_E27560FDA40BC2D5 FOREIGN KEY (schedule_id) REFERENCES schedule (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
