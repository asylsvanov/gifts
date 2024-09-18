<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221229095118 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE flow ADD import_person_from_country VARCHAR(255) DEFAULT NULL, ADD import_person_to_country VARCHAR(255) DEFAULT NULL, ADD import_person_to VARCHAR(255) DEFAULT NULL, ADD import_person_from VARCHAR(255) DEFAULT NULL, ADD import_person_to_category VARCHAR(255) DEFAULT NULL, CHANGE person_from_id person_from_id INT DEFAULT NULL, CHANGE person_to_id person_to_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE gift CHANGE origin_country origin_country VARCHAR(100) DEFAULT NULL, CHANGE category category VARCHAR(255) DEFAULT NULL, CHANGE gender gender VARCHAR(255) DEFAULT NULL, CHANGE generation generation VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE flow DROP import_person_from_country, DROP import_person_to_country, DROP import_person_to, DROP import_person_from, DROP import_person_to_category, CHANGE person_from_id person_from_id INT NOT NULL, CHANGE person_to_id person_to_id INT NOT NULL');
        $this->addSql('ALTER TABLE gift CHANGE origin_country origin_country VARCHAR(100) NOT NULL, CHANGE category category VARCHAR(255) NOT NULL, CHANGE gender gender VARCHAR(255) NOT NULL, CHANGE generation generation VARCHAR(255) NOT NULL');
    }
}
