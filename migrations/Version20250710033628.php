<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250710033628 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add the column startDate on the table event';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event ADD start_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event DROP startDate');
    }
}
