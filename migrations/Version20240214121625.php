<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240214121625 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add shift user many to many relationship';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE shift_user (shift_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', INDEX IDX_B8AAA986BB70BC0E (shift_id), INDEX IDX_B8AAA986A76ED395 (user_id), PRIMARY KEY(shift_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE shift_user ADD CONSTRAINT FK_B8AAA986BB70BC0E FOREIGN KEY (shift_id) REFERENCES shift (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE shift_user ADD CONSTRAINT FK_B8AAA986A76ED395 FOREIGN KEY (user_id) REFERENCES `users` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE shift DROP FOREIGN KEY FK_A50B3B45A76ED395');
        $this->addSql('DROP INDEX IDX_A50B3B45A76ED395 ON shift');
        $this->addSql('ALTER TABLE shift DROP user_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shift_user DROP FOREIGN KEY FK_B8AAA986BB70BC0E');
        $this->addSql('ALTER TABLE shift_user DROP FOREIGN KEY FK_B8AAA986A76ED395');
        $this->addSql('DROP TABLE shift_user');
        $this->addSql('ALTER TABLE shift ADD user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE shift ADD CONSTRAINT FK_A50B3B45A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_A50B3B45A76ED395 ON shift (user_id)');
    }
}
