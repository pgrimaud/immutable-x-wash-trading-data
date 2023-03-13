<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230313113817 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE asset (id INT AUTO_INCREMENT NOT NULL, collection_id INT NOT NULL, token_id VARCHAR(255) NOT NULL, INDEX IDX_2AF5A5C514956FD (collection_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE collection (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, asset_id INT NOT NULL, quantity VARCHAR(255) NOT NULL, token VARCHAR(255) NOT NULL, seller VARCHAR(255) NOT NULL, buyer VARCHAR(255) NOT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_F52993985DA1941 (asset_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transfer (id INT AUTO_INCREMENT NOT NULL, asset_id INT DEFAULT NULL, quantity VARCHAR(255) NOT NULL, token VARCHAR(255) NOT NULL, sender VARCHAR(255) NOT NULL, receiver VARCHAR(255) NOT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_4034A3C05DA1941 (asset_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE asset ADD CONSTRAINT FK_2AF5A5C514956FD FOREIGN KEY (collection_id) REFERENCES collection (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F52993985DA1941 FOREIGN KEY (asset_id) REFERENCES asset (id)');
        $this->addSql('ALTER TABLE transfer ADD CONSTRAINT FK_4034A3C05DA1941 FOREIGN KEY (asset_id) REFERENCES asset (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE asset DROP FOREIGN KEY FK_2AF5A5C514956FD');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F52993985DA1941');
        $this->addSql('ALTER TABLE transfer DROP FOREIGN KEY FK_4034A3C05DA1941');
        $this->addSql('DROP TABLE asset');
        $this->addSql('DROP TABLE collection');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE transfer');
    }
}
