<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260517194544 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add user_site_role + user_area_management tables, pre-assigned roles on user_invite, self-registration token on sites';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_area_management (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', area_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', assigned_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_96621B7AA76ED395 (user_id), INDEX IDX_96621B7ABD0F409C (area_id), UNIQUE INDEX uniq_user_area (user_id, area_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_invite_pre_assigned_role (user_invite_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', role_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', INDEX IDX_A5F585A1EAA1FAA3 (user_invite_id), INDEX IDX_A5F585A1D60322AC (role_id), PRIMARY KEY(user_invite_id, role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_site_role (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', site_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', role_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', assigned_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_6C13BA77A76ED395 (user_id), INDEX IDX_6C13BA77F6BD1646 (site_id), INDEX IDX_6C13BA77D60322AC (role_id), UNIQUE INDEX uniq_user_site_role (user_id, site_id, role_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_area_management ADD CONSTRAINT FK_96621B7AA76ED395 FOREIGN KEY (user_id) REFERENCES `users` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_area_management ADD CONSTRAINT FK_96621B7ABD0F409C FOREIGN KEY (area_id) REFERENCES area (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_invite_pre_assigned_role ADD CONSTRAINT FK_A5F585A1EAA1FAA3 FOREIGN KEY (user_invite_id) REFERENCES user_invite (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_invite_pre_assigned_role ADD CONSTRAINT FK_A5F585A1D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_site_role ADD CONSTRAINT FK_6C13BA77A76ED395 FOREIGN KEY (user_id) REFERENCES `users` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_site_role ADD CONSTRAINT FK_6C13BA77F6BD1646 FOREIGN KEY (site_id) REFERENCES `sites` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_site_role ADD CONSTRAINT FK_6C13BA77D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sites ADD self_registration_token VARCHAR(32) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BC00AA635E164F07 ON sites (self_registration_token)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_area_management DROP FOREIGN KEY FK_96621B7AA76ED395');
        $this->addSql('ALTER TABLE user_area_management DROP FOREIGN KEY FK_96621B7ABD0F409C');
        $this->addSql('ALTER TABLE user_invite_pre_assigned_role DROP FOREIGN KEY FK_A5F585A1EAA1FAA3');
        $this->addSql('ALTER TABLE user_invite_pre_assigned_role DROP FOREIGN KEY FK_A5F585A1D60322AC');
        $this->addSql('ALTER TABLE user_site_role DROP FOREIGN KEY FK_6C13BA77A76ED395');
        $this->addSql('ALTER TABLE user_site_role DROP FOREIGN KEY FK_6C13BA77F6BD1646');
        $this->addSql('ALTER TABLE user_site_role DROP FOREIGN KEY FK_6C13BA77D60322AC');
        $this->addSql('DROP TABLE user_area_management');
        $this->addSql('DROP TABLE user_invite_pre_assigned_role');
        $this->addSql('DROP TABLE user_site_role');
        $this->addSql('DROP INDEX UNIQ_BC00AA635E164F07 ON `sites`');
        $this->addSql('ALTER TABLE `sites` DROP self_registration_token');
    }
}
