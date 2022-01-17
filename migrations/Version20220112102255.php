<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220112102255 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE recipe_tags (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(30) NOT NULL, UNIQUE INDEX UNIQ_10A7CEF95E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE recipe_tags_links (id INT AUTO_INCREMENT NOT NULL, recipe_id INT NOT NULL, recipe_tag_id INT NOT NULL, INDEX IDX_F398D12359D8A214 (recipe_id), INDEX IDX_F398D12337CC7D30 (recipe_tag_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE recipe_tags_links ADD CONSTRAINT FK_F398D12359D8A214 FOREIGN KEY (recipe_id) REFERENCES recipe (id)');
        $this->addSql('ALTER TABLE recipe_tags_links ADD CONSTRAINT FK_F398D12337CC7D30 FOREIGN KEY (recipe_tag_id) REFERENCES recipe_tags (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recipe_tags_links DROP FOREIGN KEY FK_F398D12337CC7D30');
        $this->addSql('DROP TABLE recipe_tags');
        $this->addSql('DROP TABLE recipe_tags_links');
    }
}
