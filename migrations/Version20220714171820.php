<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220714171820 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE recipe_tags_links');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE recipe_tags_links (id INT AUTO_INCREMENT NOT NULL, recipe_id INT NOT NULL, recipe_tag_id INT NOT NULL, INDEX IDX_F398D12337CC7D30 (recipe_tag_id), INDEX IDX_F398D12359D8A214 (recipe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE recipe_tags_links ADD CONSTRAINT FK_F398D12337CC7D30 FOREIGN KEY (recipe_tag_id) REFERENCES recipe_tags (id)');
        $this->addSql('ALTER TABLE recipe_tags_links ADD CONSTRAINT FK_F398D12359D8A214 FOREIGN KEY (recipe_id) REFERENCES recipe (id)');
    }
}
