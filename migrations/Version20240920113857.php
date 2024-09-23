<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240920113857 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE flow ADD is_received TINYINT(1) DEFAULT 0');
       // $this->addSql('ALTER TABLE photo ADD CONSTRAINT FK_14B7841897A95A83 FOREIGN KEY (gift_id) REFERENCES gift (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE flow DROP is_received');
       // $this->addSql('ALTER TABLE photo DROP FOREIGN KEY FK_14B7841897A95A83');
    }
}
