<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260426153000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create classroom availability finder tables.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE timetable_upload (id INT AUTO_INCREMENT NOT NULL, original_filename VARCHAR(255) NOT NULL, stored_filename VARCHAR(255) NOT NULL, uploaded_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', week_start DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', week_end DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', total_pages INT NOT NULL, total_bookings INT NOT NULL, total_rooms INT NOT NULL, ignored_online_sessions INT NOT NULL, uses_master_room_list TINYINT(1) NOT NULL DEFAULT 0, UNIQUE INDEX UNIQ_4E0AF0B9A7E3E1E2 (stored_filename), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE `room` (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, building VARCHAR(100) DEFAULT NULL, capacity INT DEFAULT NULL, type VARCHAR(100) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_FD8E0B0E5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE room_booking (id INT AUTO_INCREMENT NOT NULL, timetable_upload_id INT NOT NULL, room_id INT NOT NULL, group_name VARCHAR(100) NOT NULL, course_name VARCHAR(255) NOT NULL, booking_date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', day_name VARCHAR(20) NOT NULL, start_time TIME NOT NULL COMMENT \'(DC2Type:time_immutable)\', end_time TIME NOT NULL COMMENT \'(DC2Type:time_immutable)\', source_page INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_A1D1F09D431D5234 (timetable_upload_id), INDEX IDX_A1D1F09DEF1EFAAA (room_id), INDEX room_booking_date_idx (booking_date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE room_conflict (id INT AUTO_INCREMENT NOT NULL, timetable_upload_id INT NOT NULL, room_id INT NOT NULL, booking_date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', start_time TIME NOT NULL COMMENT \'(DC2Type:time_immutable)\', end_time TIME NOT NULL COMMENT \'(DC2Type:time_immutable)\', description LONGTEXT NOT NULL, booking_a_group_name VARCHAR(100) NOT NULL, booking_a_course_name VARCHAR(255) NOT NULL, booking_a_source_page INT NOT NULL, booking_b_group_name VARCHAR(100) NOT NULL, booking_b_course_name VARCHAR(255) NOT NULL, booking_b_source_page INT NOT NULL, booking_a_start_time TIME DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\', booking_a_end_time TIME DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\', booking_b_start_time TIME DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\', booking_b_end_time TIME DEFAULT NULL COMMENT \'(DC2Type:time_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_3B1F66C9431D5234 (timetable_upload_id), INDEX IDX_3B1F66C9EF1EFAAA (room_id), INDEX room_conflict_date_idx (booking_date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE room_booking ADD CONSTRAINT FK_A1D1F09D431D5234 FOREIGN KEY (timetable_upload_id) REFERENCES timetable_upload (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE room_booking ADD CONSTRAINT FK_A1D1F09DEF1EFAAA FOREIGN KEY (room_id) REFERENCES `room` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE room_conflict ADD CONSTRAINT FK_3B1F66C9431D5234 FOREIGN KEY (timetable_upload_id) REFERENCES timetable_upload (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE room_conflict ADD CONSTRAINT FK_3B1F66C9EF1EFAAA FOREIGN KEY (room_id) REFERENCES `room` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE room_conflict DROP FOREIGN KEY FK_3B1F66C9431D5234');
        $this->addSql('ALTER TABLE room_conflict DROP FOREIGN KEY FK_3B1F66C9EF1EFAAA');
        $this->addSql('ALTER TABLE room_booking DROP FOREIGN KEY FK_A1D1F09D431D5234');
        $this->addSql('ALTER TABLE room_booking DROP FOREIGN KEY FK_A1D1F09DEF1EFAAA');
        $this->addSql('DROP TABLE room_conflict');
        $this->addSql('DROP TABLE room_booking');
        $this->addSql('DROP TABLE `room`');
        $this->addSql('DROP TABLE timetable_upload');
    }
}
