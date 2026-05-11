<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260511120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add job offer interview meetings for accepted applications.';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('job_offer_meeting')) {
            return;
        }

        $this->addSql('CREATE TABLE job_offer_meeting (id INT AUTO_INCREMENT NOT NULL, application_id INT NOT NULL, offer_id INT NOT NULL, student_id INT NOT NULL, partner_id INT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, room_code VARCHAR(100) NOT NULL, status VARCHAR(20) NOT NULL, scheduled_at DATETIME NOT NULL, scheduled_end_at DATETIME NOT NULL, started_at DATETIME DEFAULT NULL, ended_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX uniq_job_offer_meeting_application (application_id), INDEX idx_job_offer_meeting_offer (offer_id), INDEX idx_job_offer_meeting_student (student_id), INDEX idx_job_offer_meeting_partner (partner_id), INDEX idx_job_offer_meeting_status (status), INDEX idx_job_offer_meeting_scheduled (scheduled_at), INDEX idx_job_offer_meeting_window (scheduled_at, scheduled_end_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE job_offer_meeting ADD CONSTRAINT FK_JOB_OFFER_MEETING_APPLICATION FOREIGN KEY (application_id) REFERENCES job_application (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_offer_meeting ADD CONSTRAINT FK_JOB_OFFER_MEETING_OFFER FOREIGN KEY (offer_id) REFERENCES job_offer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_offer_meeting ADD CONSTRAINT FK_JOB_OFFER_MEETING_STUDENT FOREIGN KEY (student_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_offer_meeting ADD CONSTRAINT FK_JOB_OFFER_MEETING_PARTNER FOREIGN KEY (partner_id) REFERENCES `user` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        if (!$schema->hasTable('job_offer_meeting')) {
            return;
        }

        $this->addSql('ALTER TABLE job_offer_meeting DROP FOREIGN KEY FK_JOB_OFFER_MEETING_APPLICATION');
        $this->addSql('ALTER TABLE job_offer_meeting DROP FOREIGN KEY FK_JOB_OFFER_MEETING_OFFER');
        $this->addSql('ALTER TABLE job_offer_meeting DROP FOREIGN KEY FK_JOB_OFFER_MEETING_STUDENT');
        $this->addSql('ALTER TABLE job_offer_meeting DROP FOREIGN KEY FK_JOB_OFFER_MEETING_PARTNER');
        $this->addSql('DROP TABLE job_offer_meeting');
    }
}
