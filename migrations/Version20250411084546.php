<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250411084546 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE attendees (
              id INT AUTO_INCREMENT NOT NULL,
              name VARCHAR(255) NOT NULL,
              email VARCHAR(255) NOT NULL,
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE bookings (
              id INT AUTO_INCREMENT NOT NULL,
              event_id INT NOT NULL,
              attendee_id INT NOT NULL,
              booked_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
              INDEX IDX_7A853C3571F7E88B (event_id),
              INDEX IDX_7A853C35BCFD782A (attendee_id),
              UNIQUE INDEX event_attendee_unique (event_id, attendee_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE events (
              id INT AUTO_INCREMENT NOT NULL,
              title VARCHAR(255) NOT NULL,
              description LONGTEXT NOT NULL,
              date DATETIME NOT NULL,
              capacity INT NOT NULL,
              country VARCHAR(255) NOT NULL,
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              bookings
            ADD
              CONSTRAINT FK_7A853C3571F7E88B FOREIGN KEY (event_id) REFERENCES events (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              bookings
            ADD
              CONSTRAINT FK_7A853C35BCFD782A FOREIGN KEY (attendee_id) REFERENCES attendees (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE bookings DROP FOREIGN KEY FK_7A853C3571F7E88B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE bookings DROP FOREIGN KEY FK_7A853C35BCFD782A
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE attendees
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE bookings
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE events
        SQL);
    }
}
