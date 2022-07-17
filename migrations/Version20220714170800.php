<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220714170800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE recipe_tags_recipe (recipe_tags_id INT NOT NULL, recipe_id INT NOT NULL, INDEX IDX_6BA14263C19F41F4 (recipe_tags_id), INDEX IDX_6BA1426359D8A214 (recipe_id), PRIMARY KEY(recipe_tags_id, recipe_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE recipe_tags_recipe ADD CONSTRAINT FK_6BA14263C19F41F4 FOREIGN KEY (recipe_tags_id) REFERENCES recipe_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recipe_tags_recipe ADD CONSTRAINT FK_6BA1426359D8A214 FOREIGN KEY (recipe_id) REFERENCES recipe (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE recipe_tags_recipe');
    }
}
