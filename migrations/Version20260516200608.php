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

final class Version20260516200608 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial schema for SolidShift';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE organisation (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `sites` (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', organisation_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', name VARCHAR(45) NOT NULL, slug VARCHAR(75) NOT NULL, INDEX IDX_BC00AA639E6B1585 (organisation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `users` (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(25) DEFAULT NULL, last_name VARCHAR(25) DEFAULT NULL, phone VARCHAR(15) DEFAULT NULL, UNIQUE INDEX UNIQ_1483A5E9F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_site_access (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', site_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', role VARCHAR(15) NOT NULL, INDEX IDX_DFDC4C9EA76ED395 (user_id), INDEX IDX_DFDC4C9EF6BD1646 (site_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_invite (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', user_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:ulid)\', site_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', hash VARCHAR(125) NOT NULL, email VARCHAR(125) DEFAULT NULL, phone VARCHAR(25) DEFAULT NULL, role VARCHAR(25) NOT NULL, INDEX IDX_A2B00BEAA76ED395 (user_id), INDEX IDX_A2B00BEAF6BD1646 (site_id), INDEX IDX_A2B00BEAD1B862B8 (hash), UNIQUE INDEX UNIQ_A2B00BEAD1B862B8 (hash), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE area (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', site_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', parent_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:ulid)\', name VARCHAR(75) NOT NULL, INDEX IDX_D7943D68F6BD1646 (site_id), INDEX IDX_D7943D68727ACA70 (parent_id), UNIQUE INDEX UNIQ_D7943D685E237E06F6BD1646727ACA70 (name, site_id, parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', organisation_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', name VARCHAR(255) NOT NULL, INDEX IDX_57698A6A9E6B1585 (organisation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role_allowed_area (role_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', area_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', INDEX IDX_EE562849D60322AC (role_id), INDEX IDX_EE562849BD0F409C (area_id), PRIMARY KEY(role_id, area_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE shift_template (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', organisation_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', role_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', area_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:ulid)\', name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, start_time TIME DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\', end_time TIME DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\', required_min INT DEFAULT NULL, required_max INT DEFAULT NULL, INDEX IDX_BED487279E6B1585 (organisation_id), INDEX IDX_BED48727D60322AC (role_id), INDEX IDX_BED48727BD0F409C (area_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE schedule (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', site_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', name VARCHAR(255) NOT NULL, schedule_type VARCHAR(15) NOT NULL, start_date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', end_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', start_time TIME DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\', end_time TIME DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\', INDEX IDX_5A3811FBF6BD1646 (site_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE recurring_options (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', schedule_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', type VARCHAR(15) NOT NULL, days JSON NOT NULL, end_type VARCHAR(15) NOT NULL, end_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', end_occurrence INT DEFAULT NULL, UNIQUE INDEX UNIQ_8C36F0EA40BC2D5 (schedule_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE occurrence_template (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', schedule_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', name VARCHAR(255) NOT NULL, start_time TIME NOT NULL COMMENT \'(DC2Type:time_immutable)\', end_time TIME DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\', INDEX IDX_AC2309E4A40BC2D5 (schedule_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE occurrence (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', template_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', INDEX IDX_BEFD81F35DA0FB8 (template_id), UNIQUE INDEX UNIQ_BEFD81F35DA0FB8AA9E377A (template_id, date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE shift_requirement (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', occurrence_template_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', role_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', area_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:ulid)\', template_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:ulid)\', start_time TIME DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\', end_time TIME DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\', required_min INT DEFAULT NULL, required_max INT DEFAULT NULL, INDEX IDX_70DAD742BB0F77DB (occurrence_template_id), INDEX IDX_70DAD742D60322AC (role_id), INDEX IDX_70DAD742BD0F409C (area_id), INDEX IDX_70DAD7425DA0FB8 (template_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE shift (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', occurrence_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', requirement_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', role_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', area_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:ulid)\', user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', start_time TIME DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\', end_time TIME DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\', created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_A50B3B4530572FAC (occurrence_id), INDEX IDX_A50B3B457B576F77 (requirement_id), INDEX IDX_A50B3B45D60322AC (role_id), INDEX IDX_A50B3B45BD0F409C (area_id), INDEX IDX_A50B3B45A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE `sites` ADD CONSTRAINT FK_BC00AA639E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id)');
        $this->addSql('ALTER TABLE user_site_access ADD CONSTRAINT FK_DFDC4C9EA76ED395 FOREIGN KEY (user_id) REFERENCES `users` (id)');
        $this->addSql('ALTER TABLE user_site_access ADD CONSTRAINT FK_DFDC4C9EF6BD1646 FOREIGN KEY (site_id) REFERENCES `sites` (id)');
        $this->addSql('ALTER TABLE user_invite ADD CONSTRAINT FK_A2B00BEAA76ED395 FOREIGN KEY (user_id) REFERENCES `users` (id)');
        $this->addSql('ALTER TABLE user_invite ADD CONSTRAINT FK_A2B00BEAF6BD1646 FOREIGN KEY (site_id) REFERENCES `sites` (id)');
        $this->addSql('ALTER TABLE area ADD CONSTRAINT FK_D7943D68F6BD1646 FOREIGN KEY (site_id) REFERENCES `sites` (id)');
        $this->addSql('ALTER TABLE area ADD CONSTRAINT FK_D7943D68727ACA70 FOREIGN KEY (parent_id) REFERENCES area (id)');
        $this->addSql('ALTER TABLE role ADD CONSTRAINT FK_57698A6A9E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id)');
        $this->addSql('ALTER TABLE role_allowed_area ADD CONSTRAINT FK_EE562849D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE role_allowed_area ADD CONSTRAINT FK_EE562849BD0F409C FOREIGN KEY (area_id) REFERENCES area (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE shift_template ADD CONSTRAINT FK_BED487279E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id)');
        $this->addSql('ALTER TABLE shift_template ADD CONSTRAINT FK_BED48727D60322AC FOREIGN KEY (role_id) REFERENCES role (id)');
        $this->addSql('ALTER TABLE shift_template ADD CONSTRAINT FK_BED48727BD0F409C FOREIGN KEY (area_id) REFERENCES area (id)');
        $this->addSql('ALTER TABLE schedule ADD CONSTRAINT FK_5A3811FBF6BD1646 FOREIGN KEY (site_id) REFERENCES `sites` (id)');
        $this->addSql('ALTER TABLE recurring_options ADD CONSTRAINT FK_8C36F0EA40BC2D5 FOREIGN KEY (schedule_id) REFERENCES schedule (id)');
        $this->addSql('ALTER TABLE occurrence_template ADD CONSTRAINT FK_AC2309E4A40BC2D5 FOREIGN KEY (schedule_id) REFERENCES schedule (id)');
        $this->addSql('ALTER TABLE occurrence ADD CONSTRAINT FK_BEFD81F35DA0FB8 FOREIGN KEY (template_id) REFERENCES occurrence_template (id)');
        $this->addSql('ALTER TABLE shift_requirement ADD CONSTRAINT FK_70DAD742BB0F77DB FOREIGN KEY (occurrence_template_id) REFERENCES occurrence_template (id)');
        $this->addSql('ALTER TABLE shift_requirement ADD CONSTRAINT FK_70DAD742D60322AC FOREIGN KEY (role_id) REFERENCES role (id)');
        $this->addSql('ALTER TABLE shift_requirement ADD CONSTRAINT FK_70DAD742BD0F409C FOREIGN KEY (area_id) REFERENCES area (id)');
        $this->addSql('ALTER TABLE shift_requirement ADD CONSTRAINT FK_70DAD7425DA0FB8 FOREIGN KEY (template_id) REFERENCES shift_template (id)');
        $this->addSql('ALTER TABLE shift ADD CONSTRAINT FK_A50B3B4530572FAC FOREIGN KEY (occurrence_id) REFERENCES occurrence (id)');
        $this->addSql('ALTER TABLE shift ADD CONSTRAINT FK_A50B3B457B576F77 FOREIGN KEY (requirement_id) REFERENCES shift_requirement (id)');
        $this->addSql('ALTER TABLE shift ADD CONSTRAINT FK_A50B3B45D60322AC FOREIGN KEY (role_id) REFERENCES role (id)');
        $this->addSql('ALTER TABLE shift ADD CONSTRAINT FK_A50B3B45BD0F409C FOREIGN KEY (area_id) REFERENCES area (id)');
        $this->addSql('ALTER TABLE shift ADD CONSTRAINT FK_A50B3B45A76ED395 FOREIGN KEY (user_id) REFERENCES `users` (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE shift DROP FOREIGN KEY FK_A50B3B45A76ED395');
        $this->addSql('ALTER TABLE shift DROP FOREIGN KEY FK_A50B3B45BD0F409C');
        $this->addSql('ALTER TABLE shift DROP FOREIGN KEY FK_A50B3B45D60322AC');
        $this->addSql('ALTER TABLE shift DROP FOREIGN KEY FK_A50B3B457B576F77');
        $this->addSql('ALTER TABLE shift DROP FOREIGN KEY FK_A50B3B4530572FAC');
        $this->addSql('ALTER TABLE shift_requirement DROP FOREIGN KEY FK_70DAD7425DA0FB8');
        $this->addSql('ALTER TABLE shift_requirement DROP FOREIGN KEY FK_70DAD742BD0F409C');
        $this->addSql('ALTER TABLE shift_requirement DROP FOREIGN KEY FK_70DAD742D60322AC');
        $this->addSql('ALTER TABLE shift_requirement DROP FOREIGN KEY FK_70DAD742BB0F77DB');
        $this->addSql('ALTER TABLE occurrence DROP FOREIGN KEY FK_BEFD81F35DA0FB8');
        $this->addSql('ALTER TABLE occurrence_template DROP FOREIGN KEY FK_AC2309E4A40BC2D5');
        $this->addSql('ALTER TABLE recurring_options DROP FOREIGN KEY FK_8C36F0EA40BC2D5');
        $this->addSql('ALTER TABLE schedule DROP FOREIGN KEY FK_5A3811FBF6BD1646');
        $this->addSql('ALTER TABLE shift_template DROP FOREIGN KEY FK_BED48727BD0F409C');
        $this->addSql('ALTER TABLE shift_template DROP FOREIGN KEY FK_BED48727D60322AC');
        $this->addSql('ALTER TABLE shift_template DROP FOREIGN KEY FK_BED487279E6B1585');
        $this->addSql('ALTER TABLE role_allowed_area DROP FOREIGN KEY FK_EE562849BD0F409C');
        $this->addSql('ALTER TABLE role_allowed_area DROP FOREIGN KEY FK_EE562849D60322AC');
        $this->addSql('ALTER TABLE role DROP FOREIGN KEY FK_57698A6A9E6B1585');
        $this->addSql('ALTER TABLE area DROP FOREIGN KEY FK_D7943D68727ACA70');
        $this->addSql('ALTER TABLE area DROP FOREIGN KEY FK_D7943D68F6BD1646');
        $this->addSql('ALTER TABLE user_invite DROP FOREIGN KEY FK_A2B00BEAF6BD1646');
        $this->addSql('ALTER TABLE user_invite DROP FOREIGN KEY FK_A2B00BEAA76ED395');
        $this->addSql('ALTER TABLE user_site_access DROP FOREIGN KEY FK_DFDC4C9EF6BD1646');
        $this->addSql('ALTER TABLE user_site_access DROP FOREIGN KEY FK_DFDC4C9EA76ED395');
        $this->addSql('ALTER TABLE `sites` DROP FOREIGN KEY FK_BC00AA639E6B1585');

        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('DROP TABLE shift');
        $this->addSql('DROP TABLE shift_requirement');
        $this->addSql('DROP TABLE occurrence');
        $this->addSql('DROP TABLE occurrence_template');
        $this->addSql('DROP TABLE recurring_options');
        $this->addSql('DROP TABLE schedule');
        $this->addSql('DROP TABLE shift_template');
        $this->addSql('DROP TABLE role_allowed_area');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE area');
        $this->addSql('DROP TABLE user_invite');
        $this->addSql('DROP TABLE user_site_access');
        $this->addSql('DROP TABLE `users`');
        $this->addSql('DROP TABLE `sites`');
        $this->addSql('DROP TABLE organisation');
    }
}
