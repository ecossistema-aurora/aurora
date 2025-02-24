<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250214133735 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the table social networks';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE social_network (id UUID NOT NULL, code integer NOT NULL, base_url VARCHAR(50) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN social_network.id IS \'(DC2Type:uuid)\'');

        $this->addSql(<<<SQL
                INSERT INTO social_network (id, code, base_url) VALUES
                ('5c151480-6230-4b57-8276-d7a8667a8ad5', 1, 'https://facebook.com/'),
                ('62d69b0a-543b-470e-bf15-53df16223dd5', 2, 'https://instagram.com/'),
                ('a1a4d897-8319-47c5-98d9-516db20db52e', 3, 'https://linkedin.com/'),
                ('c5901550-77dd-4479-a9af-165556e740d0', 4, 'https://pinterest.com/'),
                ('2bb86482-d2f9-4728-ad8f-e76af872ccbe', 5, 'https://spotify.com/'),
                ('849eca3e-9436-4fec-97f5-40b3c5a1243b', 6, 'https://vimeo.com/'),
                ('95181741-93cc-4d85-be76-6330254ce8c6', 7, 'https://tiktok.com/'),
                ('9c747003-ca61-405e-99bb-359f4fb0e8ea', 8, 'https://x.com/'),
                ('321752bc-cc65-417b-a350-b52e49d40776', 9, 'https://youtube.com/');
            SQL);

        $this->addSql('CREATE TABLE agent_social_network (agent_id UUID NOT NULL, social_network_id UUID NOT NULL, value VARCHAR(100) NOT NULL, PRIMARY KEY(agent_id, social_network_id))');
        $this->addSql('COMMENT ON COLUMN agent_social_network.agent_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN agent_social_network.social_network_id IS \'(DC2Type:uuid)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE social_network');
        $this->addSql('DROP TABLE agent_social_network');
    }
}
