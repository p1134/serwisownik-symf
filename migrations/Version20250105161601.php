<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250105161601 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE repair DROP FOREIGN KEY repair_ibfk_1');
        $this->addSql('ALTER TABLE repair ADD CONSTRAINT FK_8EE43421545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id)');
        $this->addSql('ALTER TABLE vehicle ADD insurance DATE NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vehicle DROP insurance');
        $this->addSql('ALTER TABLE repair DROP FOREIGN KEY FK_8EE43421545317D1');
        $this->addSql('ALTER TABLE repair ADD CONSTRAINT repair_ibfk_1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) ON UPDATE CASCADE ON DELETE CASCADE');
    }
}
