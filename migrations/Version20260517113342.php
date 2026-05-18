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

final class Version20260517113342 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop schedule.start_time / schedule.end_time (replaced by OccurrenceTemplate times)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE schedule DROP start_time, DROP end_time');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE schedule ADD start_time TIME DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\', ADD end_time TIME DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\'');
    }
}
