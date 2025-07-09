<?php

declare(strict_types=1);

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250707122855 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE schedule_location (schedule_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', location_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', INDEX IDX_E27560FDA40BC2D5 (schedule_id), INDEX IDX_E27560FD64D218E (location_id), PRIMARY KEY(schedule_id, location_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE schedule_location ADD CONSTRAINT FK_E27560FDA40BC2D5 FOREIGN KEY (schedule_id) REFERENCES schedule (id)');
        $this->addSql('ALTER TABLE schedule_location ADD CONSTRAINT FK_E27560FD64D218E FOREIGN KEY (location_id) REFERENCES location (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE schedule DROP FOREIGN KEY FK_5A3811FB64D218E');
        $this->addSql('DROP INDEX IDX_5A3811FB64D218E ON schedule');
        $this->addSql('ALTER TABLE schedule DROP location_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE schedule_location DROP FOREIGN KEY FK_E27560FDA40BC2D5');
        $this->addSql('ALTER TABLE schedule_location DROP FOREIGN KEY FK_E27560FD64D218E');
        $this->addSql('DROP TABLE schedule_location');
        $this->addSql('ALTER TABLE schedule ADD location_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE schedule ADD CONSTRAINT FK_5A3811FB64D218E FOREIGN KEY (location_id) REFERENCES location (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_5A3811FB64D218E ON schedule (location_id)');
    }
}
