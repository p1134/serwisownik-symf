<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250212072143 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE raport (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, file LONGBLOB NOT NULL, date_create DATE NOT NULL, INDEX IDX_31AFD144A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE raport ADD CONSTRAINT FK_31AFD144A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE repair CHANGE date_repair date_repair DATE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE raport DROP FOREIGN KEY FK_31AFD144A76ED395');
        $this->addSql('DROP TABLE raport');
        $this->addSql('ALTER TABLE repair CHANGE date_repair date_repair DATE NOT NULL');
    }
}
