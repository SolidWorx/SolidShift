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
final class Version20240120115303 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add site to schedule';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE schedule ADD site_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE schedule ADD CONSTRAINT FK_5A3811FBF6BD1646 FOREIGN KEY (site_id) REFERENCES `sites` (id)');
        $this->addSql('CREATE INDEX IDX_5A3811FBF6BD1646 ON schedule (site_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE schedule DROP FOREIGN KEY FK_5A3811FBF6BD1646');
        $this->addSql('DROP INDEX IDX_5A3811FBF6BD1646 ON schedule');
        $this->addSql('ALTER TABLE schedule DROP site_id');
    }
}
