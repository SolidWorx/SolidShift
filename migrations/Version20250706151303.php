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
final class Version20250706151303 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE organisation (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sites ADD organisation_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE sites ADD CONSTRAINT FK_BC00AA639E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id)');
        $this->addSql('CREATE INDEX IDX_BC00AA639E6B1585 ON sites (organisation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `sites` DROP FOREIGN KEY FK_BC00AA639E6B1585');
        $this->addSql('DROP TABLE organisation');
        $this->addSql('DROP INDEX IDX_BC00AA639E6B1585 ON `sites`');
        $this->addSql('ALTER TABLE `sites` DROP organisation_id');
    }
}
