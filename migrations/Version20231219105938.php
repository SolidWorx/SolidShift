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
final class Version20231219105938 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE location (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', parent_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:ulid)\', site_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', name VARCHAR(75) NOT NULL, INDEX IDX_5E9E89CB727ACA70 (parent_id), INDEX IDX_5E9E89CBF6BD1646 (site_id), UNIQUE INDEX UNIQ_5E9E89CB5E237E06F6BD1646727ACA70 (name, site_id, parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CB727ACA70 FOREIGN KEY (parent_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CBF6BD1646 FOREIGN KEY (site_id) REFERENCES `sites` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE location DROP FOREIGN KEY FK_5E9E89CB727ACA70');
        $this->addSql('ALTER TABLE location DROP FOREIGN KEY FK_5E9E89CBF6BD1646');
        $this->addSql('DROP TABLE location');
    }
}
