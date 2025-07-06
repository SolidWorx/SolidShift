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
final class Version20240101180042 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add schedule and shift tables';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE schedule (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', location_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', start_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', end_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', start_time TIME DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\', end_time TIME DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\', recurring_options JSON DEFAULT NULL, INDEX IDX_5A3811FB64D218E (location_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE shift (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', schedule_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', location_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', start_date DATETIME NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', end_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', start_time TIME NOT NULL COMMENT \'(DC2Type:time_immutable)\', end_time TIME DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\', INDEX IDX_A50B3B45A76ED395 (user_id), INDEX IDX_A50B3B45A40BC2D5 (schedule_id), INDEX IDX_A50B3B4564D218E (location_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE schedule ADD CONSTRAINT FK_5A3811FB64D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE shift ADD CONSTRAINT FK_A50B3B45A76ED395 FOREIGN KEY (user_id) REFERENCES `users` (id)');
        $this->addSql('ALTER TABLE shift ADD CONSTRAINT FK_A50B3B45A40BC2D5 FOREIGN KEY (schedule_id) REFERENCES schedule (id)');
        $this->addSql('ALTER TABLE shift ADD CONSTRAINT FK_A50B3B4564D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A2B00BEAD1B862B8 ON user_invite (hash)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE schedule DROP FOREIGN KEY FK_5A3811FB64D218E');
        $this->addSql('ALTER TABLE shift DROP FOREIGN KEY FK_A50B3B45A76ED395');
        $this->addSql('ALTER TABLE shift DROP FOREIGN KEY FK_A50B3B45A40BC2D5');
        $this->addSql('ALTER TABLE shift DROP FOREIGN KEY FK_A50B3B4564D218E');
        $this->addSql('DROP TABLE schedule');
        $this->addSql('DROP TABLE shift');
        $this->addSql('DROP INDEX UNIQ_A2B00BEAD1B862B8 ON user_invite');
    }
}
