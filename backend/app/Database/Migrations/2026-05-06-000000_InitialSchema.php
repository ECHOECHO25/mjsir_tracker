<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class InitialSchema extends Migration
{
    public function up()
    {
        // 1. Users Table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'unique'     => true,
            ],
            'password' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'role' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'failed_attempts' => [
                'type'       => 'INT',
                'default'    => 0,
            ],
            'locked_until' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('users', true);

        // 2. Notifications Table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'target_user' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'message' => [
                'type' => 'TEXT',
            ],
            'is_read' => [
                'type'       => 'TINYINT',
                'constraint' => '1',
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('notifications', true);

        // 3. Manuscripts Table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'code' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'type' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'contributor_type' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'submission_form' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'year' => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => true],
            'date' => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => true],
            'title' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'link' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'inst' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'authors' => ['type' => 'TEXT', 'null' => true],
            'author_email' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'file_path' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'repo' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'editor' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'preAction' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'turnitin' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'aiWriting' => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => true],
            'prenotes' => ['type' => 'TEXT', 'null' => true],
            'revStatus' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'summary' => ['type' => 'TEXT', 'null' => true],
            'eic' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'dret' => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => true],
            'drev' => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => true],
            'dacc' => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => true],
            'criteria_scope' => ['type' => 'TINYINT', 'constraint' => '1', 'default' => 0],
            'criteria_methodology' => ['type' => 'TINYINT', 'constraint' => '1', 'default' => 0],
            'criteria_format' => ['type' => 'TINYINT', 'constraint' => '1', 'default' => 0],
            'manuscript_type' => ['type' => 'VARCHAR', 'constraint' => '100', 'null' => true],
            'turnitin_file' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'turnitin_link' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'editor_comments' => ['type' => 'TEXT', 'null' => true],
            'editor_remarks' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'prescreen_status' => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => true],
            'final_decision' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'date_accepted' => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => true],
            'date_revised' => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('manuscripts', true);

        // 4. Manuscript Reviewers Table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'manuscript_id' => [
                'type'       => 'INT',
            ],
            'name' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'affiliation' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'email' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('manuscript_reviewers', true);

        // 5. Reviewer Rounds Table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'reviewer_id' => [
                'type'       => 'INT',
            ],
            'round_number' => [
                'type'       => 'INT',
            ],
            'status' => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => true],
            'date_sent' => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => true],
            'sent_file_path' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'sent_file_link' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'file_path' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'file_link' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('reviewer_rounds', true);

        // 6. Manuscript Rounds Table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'manuscript_id' => [
                'type'       => 'INT',
            ],
            'round_number' => [
                'type'       => 'INT',
            ],
            'revised_file_path' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'revised_link' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'action_taken_file_path' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'action_taken_link' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'action_taken' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('manuscript_rounds', true);
    }

    public function down()
    {
        $this->forge->dropTable('reviewer_rounds', true);
        $this->forge->dropTable('manuscript_rounds', true);
        $this->forge->dropTable('manuscript_reviewers', true);
        $this->forge->dropTable('manuscripts', true);
        $this->forge->dropTable('notifications', true);
        $this->forge->dropTable('users', true);
    }
}
