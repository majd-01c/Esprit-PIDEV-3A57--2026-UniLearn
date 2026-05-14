<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260514125500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Synchronize user_answer anti-cheat columns with the Java desktop app';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('user_answer');
        $hasCheatFlag = $table->hasColumn('cheat_flag');
        $hasIsCheated = $table->hasColumn('is_cheated');

        if ($hasCheatFlag && $hasIsCheated) {
            $this->addSql('UPDATE user_answer SET cheat_flag = 1 WHERE is_cheated = 1');
            $this->addSql('ALTER TABLE user_answer DROP is_cheated');
            $this->addSql('ALTER TABLE user_answer CHANGE cheat_flag cheat_flag TINYINT DEFAULT 0 NOT NULL');
        } elseif ($hasIsCheated) {
            $this->addSql('ALTER TABLE user_answer CHANGE is_cheated cheat_flag TINYINT DEFAULT 0 NOT NULL');
        } elseif ($hasCheatFlag) {
            $this->addSql('ALTER TABLE user_answer CHANGE cheat_flag cheat_flag TINYINT DEFAULT 0 NOT NULL');
        } else {
            $this->addSql('ALTER TABLE user_answer ADD cheat_flag TINYINT DEFAULT 0 NOT NULL');
        }

        if (!$table->hasColumn('tab_switch_count')) {
            $this->addSql('ALTER TABLE user_answer ADD tab_switch_count INT DEFAULT 0 NOT NULL');
        } else {
            $this->addSql('ALTER TABLE user_answer CHANGE tab_switch_count tab_switch_count INT DEFAULT 0 NOT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable('user_answer');

        if ($table->hasColumn('cheat_flag') && !$table->hasColumn('is_cheated')) {
            $this->addSql('ALTER TABLE user_answer CHANGE cheat_flag is_cheated TINYINT DEFAULT 0 NOT NULL');
        }
    }
}
