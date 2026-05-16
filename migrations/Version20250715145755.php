<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250715145755 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE schedule_schedule_shift (schedule_id BINARY(16) NOT NULL, schedule_shift_id BINARY(16) NOT NULL, INDEX IDX_638F7B4AA40BC2D5 (schedule_id), INDEX IDX_638F7B4A79B5538 (schedule_shift_id), PRIMARY KEY (schedule_id, schedule_shift_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE schedule_shift (id BINARY(16) NOT NULL, start_time TIME NOT NULL, end_time TIME DEFAULT NULL, location_id BINARY(16) DEFAULT NULL, position_id INT DEFAULT NULL, INDEX IDX_5952FB1964D218E (location_id), INDEX IDX_5952FB19DD842E46 (position_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE schedule_schedule_shift ADD CONSTRAINT FK_638F7B4AA40BC2D5 FOREIGN KEY (schedule_id) REFERENCES schedule (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE schedule_schedule_shift ADD CONSTRAINT FK_638F7B4A79B5538 FOREIGN KEY (schedule_shift_id) REFERENCES schedule_shift (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE schedule_shift ADD CONSTRAINT FK_5952FB1964D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE schedule_shift ADD CONSTRAINT FK_5952FB19DD842E46 FOREIGN KEY (position_id) REFERENCES position (id)');
        $this->addSql('ALTER TABLE shift_assignment DROP FOREIGN KEY FK_47A0F95A76ED395');
        $this->addSql('ALTER TABLE shift_assignment DROP FOREIGN KEY FK_47A0F95BB70BC0E');
        $this->addSql('DROP TABLE shift_assignment');
        $this->addSql('ALTER TABLE shift ADD user_id BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE position CHANGE id id BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE schedule_shift CHANGE position_id position_id BINARY(16) DEFAULT NULL');
        $this->addSql('ALTER TABLE shift ADD CONSTRAINT FK_A50B3B45A76ED395 FOREIGN KEY (user_id) REFERENCES `users` (id)');
        $this->addSql('CREATE INDEX IDX_A50B3B45A76ED395 ON shift (user_id)');
        $this->addSql('ALTER TABLE shift_template CHANGE position_id position_id BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE shift ADD CONSTRAINT FK_A50B3B45A76ED395 FOREIGN KEY (user_id) REFERENCES `users` (id)');
        $this->addSql('CREATE INDEX IDX_A50B3B45A76ED395 ON shift (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE position CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE schedule_shift CHANGE position_id position_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE shift DROP FOREIGN KEY FK_A50B3B45A76ED395');
        $this->addSql('DROP INDEX IDX_A50B3B45A76ED395 ON shift');
        $this->addSql('ALTER TABLE shift_template CHANGE position_id position_id INT NOT NULL');
        $this->addSql('CREATE TABLE shift_assignment (id BINARY(16) NOT NULL, shift_id BINARY(16) NOT NULL, user_id BINARY(16) NOT NULL, INDEX IDX_47A0F95A76ED395 (user_id), INDEX IDX_47A0F95BB70BC0E (shift_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE shift_assignment ADD CONSTRAINT FK_47A0F95A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE shift_assignment ADD CONSTRAINT FK_47A0F95BB70BC0E FOREIGN KEY (shift_id) REFERENCES shift (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE schedule_schedule_shift DROP FOREIGN KEY FK_638F7B4AA40BC2D5');
        $this->addSql('ALTER TABLE schedule_schedule_shift DROP FOREIGN KEY FK_638F7B4A79B5538');
        $this->addSql('ALTER TABLE schedule_shift DROP FOREIGN KEY FK_5952FB1964D218E');
        $this->addSql('ALTER TABLE schedule_shift DROP FOREIGN KEY FK_5952FB19DD842E46');
        $this->addSql('DROP TABLE schedule_schedule_shift');
        $this->addSql('DROP TABLE schedule_shift');
        $this->addSql('ALTER TABLE shift DROP FOREIGN KEY FK_A50B3B45A76ED395');
        $this->addSql('DROP INDEX IDX_A50B3B45A76ED395 ON shift');
        $this->addSql('ALTER TABLE shift DROP user_id');
    }
}
