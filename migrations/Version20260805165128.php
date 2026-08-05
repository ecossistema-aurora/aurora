<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260805165128 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adicionando tags em organizações';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE tag_organizations (organization_id UUID NOT NULL, tag_id UUID NOT NULL, PRIMARY KEY(organization_id, tag_id))');
        $this->addSql('CREATE INDEX IDX_ACD26B7832C8A3DE ON tag_organizations (organization_id)');
        $this->addSql('CREATE INDEX IDX_ACD26B78BAD26311 ON tag_organizations (tag_id)');
        $this->addSql('COMMENT ON COLUMN tag_organizations.organization_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN tag_organizations.tag_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE tag_organizations ADD CONSTRAINT fk_tag_organizations_organization_organization_id FOREIGN KEY (organization_id) REFERENCES organization (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tag_organizations ADD CONSTRAINT fk_tag_organizations_tag_tag_id FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tag_organizations DROP CONSTRAINT fk_tag_organizations_organization_organization_id');
        $this->addSql('ALTER TABLE tag_organizations DROP CONSTRAINT fk_tag_organizations_tag_tag_id');
        $this->addSql('DROP TABLE tag_organizations');
    }
}
