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
final class Version20240119054433 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add recurring options to schedule';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE recurring_options (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', schedule_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', type VARCHAR(15) NOT NULL, days JSON NOT NULL, end_type VARCHAR(15) NOT NULL, end_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', end_occurrence INT DEFAULT NULL, UNIQUE INDEX UNIQ_8C36F0EA40BC2D5 (schedule_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE recurring_options ADD CONSTRAINT FK_8C36F0EA40BC2D5 FOREIGN KEY (schedule_id) REFERENCES schedule (id)');
        $this->addSql('ALTER TABLE schedule ADD schedule_type VARCHAR(15) NOT NULL, DROP recurring_options, CHANGE start_date start_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', CHANGE end_date end_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE shift CHANGE start_date start_date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', CHANGE end_date end_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recurring_options DROP FOREIGN KEY FK_8C36F0EA40BC2D5');
        $this->addSql('DROP TABLE recurring_options');
        $this->addSql('ALTER TABLE shift CHANGE start_date start_date DATETIME NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', CHANGE end_date end_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE schedule ADD recurring_options JSON DEFAULT NULL, DROP schedule_type, CHANGE start_date start_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', CHANGE end_date end_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\'');
    }
}
