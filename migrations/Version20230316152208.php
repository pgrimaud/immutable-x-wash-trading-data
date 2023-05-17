<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230316152208 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_4034A3C0BFDFB4D8 ON transfer');
        $this->addSql('ALTER TABLE transfer CHANGE internal_id transaction_id INT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4034A3C02FC0CB0F ON transfer (transaction_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_4034A3C02FC0CB0F ON transfer');
        $this->addSql('ALTER TABLE transfer CHANGE transaction_id internal_id INT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4034A3C0BFDFB4D8 ON transfer (internal_id)');
    }
}
