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

final class Version20231227082957 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add role to user invite';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_invite ADD role VARCHAR(25) NOT NULL');
        $this->addSql('CREATE INDEX IDX_A2B00BEAD1B862B8 ON user_invite (hash)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_A2B00BEAD1B862B8 ON user_invite');
        $this->addSql('ALTER TABLE user_invite DROP role');
    }
}
