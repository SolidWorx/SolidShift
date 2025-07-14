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
final class Version20250714193319 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE shift_template (id BINARY(16) NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, start_time TIME DEFAULT NULL, end_time TIME DEFAULT NULL, required_min INT DEFAULT NULL, required_max INT DEFAULT NULL, position_id INT NOT NULL, location_id BINARY(16) NOT NULL, INDEX IDX_BED48727DD842E46 (position_id), INDEX IDX_BED4872764D218E (location_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE shift_template ADD CONSTRAINT FK_BED48727DD842E46 FOREIGN KEY (position_id) REFERENCES position (id)');
        $this->addSql('ALTER TABLE shift_template ADD CONSTRAINT FK_BED4872764D218E FOREIGN KEY (location_id) REFERENCES location (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shift_template DROP FOREIGN KEY FK_BED48727DD842E46');
        $this->addSql('ALTER TABLE shift_template DROP FOREIGN KEY FK_BED4872764D218E');
        $this->addSql('DROP TABLE shift_template');
    }
}
