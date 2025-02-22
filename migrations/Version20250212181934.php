<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250212181934 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the table link entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE link_entity (id UUID NOT NULL, entity_id UUID NOT NULL, entity_type INTEGER NOT NULL, agent BOOLEAN DEFAULT FALSE, event BOOLEAN DEFAULT FALSE, initiative BOOLEAN DEFAULT FALSE, space BOOLEAN DEFAULT FALSE, opportunity BOOLEAN DEFAULT FALSE, organization BOOLEAN DEFAULT FALSE, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN link_entity.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN link_entity.entity_id IS \'(DC2Type:uuid)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE link_entity');
    }
}
