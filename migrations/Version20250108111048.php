<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250108111048 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE repair (id INT AUTO_INCREMENT NOT NULL, vehicle_id INT NOT NULL, user_id INT NOT NULL, part VARCHAR(255) NOT NULL, price NUMERIC(10, 0) NOT NULL, date_repair DATE DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, status VARCHAR(255) NOT NULL, INDEX IDX_8EE43421545317D1 (vehicle_id), INDEX IDX_8EE43421A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, is_verified TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vehicle (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, brand VARCHAR(255) NOT NULL, model VARCHAR(255) NOT NULL, year INT NOT NULL, number_plate VARCHAR(255) NOT NULL, date_purchase INT NOT NULL, service DATE NOT NULL, insurance DATE NOT NULL, INDEX IDX_1B80E4867E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE repair ADD CONSTRAINT FK_8EE43421545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id)');
        $this->addSql('ALTER TABLE repair ADD CONSTRAINT FK_8EE43421A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE vehicle ADD CONSTRAINT FK_1B80E4867E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE repair DROP FOREIGN KEY FK_8EE43421545317D1');
        $this->addSql('ALTER TABLE repair DROP FOREIGN KEY FK_8EE43421A76ED395');
        $this->addSql('ALTER TABLE vehicle DROP FOREIGN KEY FK_1B80E4867E3C61F9');
        $this->addSql('DROP TABLE repair');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE vehicle');
    }
}
