<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251021151918 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE artist_track (artist_id INT NOT NULL, track_id INT NOT NULL, INDEX IDX_B6EFC8F5B7970CF8 (artist_id), INDEX IDX_B6EFC8F55ED23C43 (track_id), PRIMARY KEY(artist_id, track_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE artist_track ADD CONSTRAINT FK_B6EFC8F5B7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE artist_track ADD CONSTRAINT FK_B6EFC8F55ED23C43 FOREIGN KEY (track_id) REFERENCES track (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE artist_track DROP FOREIGN KEY FK_B6EFC8F5B7970CF8');
        $this->addSql('ALTER TABLE artist_track DROP FOREIGN KEY FK_B6EFC8F55ED23C43');
        $this->addSql('DROP TABLE artist_track');
    }
}
