<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210729212436 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointments CHANGE ap_personal_id ap_personal_id INT DEFAULT NULL, CHANGE ap_reservation_date ap_reservation_date DATE DEFAULT NULL, CHANGE ap_reservation_hour ap_reservation_hour TIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointments CHANGE ap_personal_id ap_personal_id INT NOT NULL, CHANGE ap_reservation_date ap_reservation_date DATE NOT NULL, CHANGE ap_reservation_hour ap_reservation_hour TIME NOT NULL');
    }
}
