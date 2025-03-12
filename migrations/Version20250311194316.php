<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250311194316 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('alter table space add column link_entity text[]');

        $this->addSql("update space set link_entity = array_append(space.link_entity, 'agent') where space.id in (select entity_id from link_entity where link_entity.entity_type = 4 and link_entity.agent is true)");
        $this->addSql("update space set link_entity = array_append(space.link_entity, 'event') where space.id in (select entity_id from link_entity where link_entity.entity_type = 4 and link_entity.event is true)");
        $this->addSql("update space set link_entity = array_append(space.link_entity, 'initiative') where space.id in (select entity_id from link_entity where link_entity.entity_type = 4 and link_entity.initiative is true)");
        $this->addSql("update space set link_entity = array_append(space.link_entity, 'space') where space.id in (select entity_id from link_entity where link_entity.entity_type = 4 and link_entity.space is true)");
        $this->addSql("update space set link_entity = array_append(space.link_entity, 'opportunity') where space.id in (select entity_id from link_entity where link_entity.entity_type = 4 and link_entity.opportunity is true)");
        $this->addSql("update space set link_entity = array_append(space.link_entity, 'organization') where space.id in (select entity_id from link_entity where link_entity.entity_type = 4 and link_entity.organization is true)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('alter table space drop column link_entity');
    }
}
