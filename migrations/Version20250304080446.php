<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250304080446 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE unit_order (unit_id INT NOT NULL, order_id INT NOT NULL, INDEX IDX_7E1BED93F8BD700D (unit_id), INDEX IDX_7E1BED938D9F6D38 (order_id), PRIMARY KEY(unit_id, order_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE unit_order ADD CONSTRAINT FK_7E1BED93F8BD700D FOREIGN KEY (unit_id) REFERENCES unit (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE unit_order ADD CONSTRAINT FK_7E1BED938D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE unit DROP FOREIGN KEY FK_DCBB0C5310E71712');
        $this->addSql('DROP INDEX IDX_DCBB0C5310E71712 ON unit');
        $this->addSql('ALTER TABLE unit DROP current_order_id');
        $this->addSql('ALTER TABLE user ADD roles JSON NOT NULL, ADD attempts SMALLINT NOT NULL, DROP role');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE unit_order DROP FOREIGN KEY FK_7E1BED93F8BD700D');
        $this->addSql('ALTER TABLE unit_order DROP FOREIGN KEY FK_7E1BED938D9F6D38');
        $this->addSql('DROP TABLE unit_order');
        $this->addSql('ALTER TABLE unit ADD current_order_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE unit ADD CONSTRAINT FK_DCBB0C5310E71712 FOREIGN KEY (current_order_id) REFERENCES `order` (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_DCBB0C5310E71712 ON unit (current_order_id)');
        $this->addSql('ALTER TABLE user ADD role VARCHAR(255) NOT NULL, DROP roles, DROP attempts');
    }
}
