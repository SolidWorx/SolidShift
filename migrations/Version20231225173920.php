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

final class Version20231225173920 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add user invite table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_invite (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', user_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:ulid)\', site_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', hash VARCHAR(125) NOT NULL, email VARCHAR(125) DEFAULT NULL, phone VARCHAR(25) DEFAULT NULL, INDEX IDX_A2B00BEAA76ED395 (user_id), INDEX IDX_A2B00BEAF6BD1646 (site_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_invite ADD CONSTRAINT FK_A2B00BEAA76ED395 FOREIGN KEY (user_id) REFERENCES `users` (id)');
        $this->addSql('ALTER TABLE user_invite ADD CONSTRAINT FK_A2B00BEAF6BD1646 FOREIGN KEY (site_id) REFERENCES `sites` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_invite DROP FOREIGN KEY FK_A2B00BEAA76ED395');
        $this->addSql('ALTER TABLE user_invite DROP FOREIGN KEY FK_A2B00BEAF6BD1646');
        $this->addSql('DROP TABLE user_invite');
    }
}
