<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    public function run()
    {
        // 1. Seed Sample Users (REPO and Associate Editors)
        $users = [
            [
                'name'     => 'REPO Staff One',
                'email'    => 'repo@mjsir.com',
                'password' => password_hash('Repo123!', PASSWORD_DEFAULT),
                'role'     => 'repo',
            ],
            [
                'name'     => 'Dr. Associate Editor 1',
                'email'    => 'ae1@mjsir.com',
                'password' => password_hash('Editor123!', PASSWORD_DEFAULT),
                'role'     => 'editor',
            ],
            [
                'name'     => 'Dr. Associate Editor 2',
                'email'    => 'ae2@mjsir.com',
                'password' => password_hash('Editor123!', PASSWORD_DEFAULT),
                'role'     => 'editor',
            ],
        ];

        $this->db->table('users')->insertBatch($users);

        // 2. Seed Sample Manuscripts
        $manuscripts = [
            // Manuscript 1: Pending Prescreening (Brand New)
            [
                'code' => 'MJSIR-2026-001',
                'title' => 'Exploring the Impact of Agentic AI on Software Engineering',
                'authors' => 'John Doe, Jane Smith',
                'author_email' => 'author1@example.com',
                'type' => 'Research Article',
                'manuscript_type' => 'Research Article',
                'contributor_type' => 'External',
                'year' => '2026',
                'date' => date('Y-m-d'),
                'inst' => 'Tech University',
                'prescreen_status' => 'Pending',
                'review_round' => 1,
            ],
            // Manuscript 2: Assigned to AE1 (In Review)
            [
                'code' => 'MJSIR-2026-002',
                'title' => 'Advancements in Quantum Machine Learning Algorithms',
                'authors' => 'Alice Johnson',
                'author_email' => 'alice@quantum.edu',
                'type' => 'Review Article',
                'manuscript_type' => 'Review Article',
                'contributor_type' => 'Internal',
                'year' => '2026',
                'date' => date('Y-m-d', strtotime('-5 days')),
                'inst' => 'Quantum Institute',
                'prescreen_status' => 'Passed',
                'editor' => 'Dr. Associate Editor 1', // Assigned AE
                'revStatus' => 'In Review',
                'review_round' => 1,
                'suggested_reviewers' => 'Reviewer A (revA@test.com), Reviewer B (revB@test.com)',
            ],
            // Manuscript 3: Revision Requested by EIC (Round 2)
            [
                'code' => 'MJSIR-2026-003',
                'title' => 'A Comprehensive Study on Renewable Energy Policies',
                'authors' => 'Robert Brown',
                'author_email' => 'robert.b@energy.org',
                'type' => 'Research Article',
                'manuscript_type' => 'Research Article',
                'contributor_type' => 'External',
                'year' => '2026',
                'date' => date('Y-m-d', strtotime('-15 days')),
                'inst' => 'Global Energy Council',
                'prescreen_status' => 'Passed',
                'editor' => 'Dr. Associate Editor 2',
                'revStatus' => 'Revision Requested',
                'editor_comments' => 'Please expand on the data in section 3.',
                'review_round' => 2,
            ],
            // Manuscript 4: Accepted
            [
                'code' => 'MJSIR-2026-004',
                'title' => 'Microplastics in Urban Water Systems: A Local Study',
                'authors' => 'Maria Garcia',
                'author_email' => 'm.garcia@eco.org',
                'type' => 'Case Study',
                'manuscript_type' => 'Case Study',
                'contributor_type' => 'Internal',
                'year' => '2026',
                'date' => date('Y-m-d', strtotime('-30 days')),
                'inst' => 'City Environmental Lab',
                'prescreen_status' => 'Passed',
                'editor' => 'Dr. Associate Editor 1',
                'revStatus' => 'Accepted',
                'final_decision' => 'Accepted',
                'date_accepted' => date('Y-m-d', strtotime('-2 days')),
                'review_round' => 1,
            ],
            // Manuscript 5: Rejected
            [
                'code' => 'MJSIR-2026-005',
                'title' => 'Theoretical Applications of Time Travel Mechanics',
                'authors' => 'Doc Brown',
                'author_email' => 'doc@future.com',
                'type' => 'Short Communication',
                'manuscript_type' => 'Short Communication',
                'contributor_type' => 'External',
                'year' => '2026',
                'date' => date('Y-m-d', strtotime('-10 days')),
                'inst' => 'Hill Valley College',
                'prescreen_status' => 'Rejected',
                'action_taken' => 'Rejection Letter Sent',
                'editor_remarks' => 'Out of scope for this journal.',
                'review_round' => 1,
            ],
        ];

        foreach ($manuscripts as $idx => $manuscript) {
            $round = isset($manuscript['review_round']) ? $manuscript['review_round'] : 1;
            $reviewers = isset($manuscript['suggested_reviewers']) ? $manuscript['suggested_reviewers'] : null;
            $action_taken = isset($manuscript['action_taken']) ? $manuscript['action_taken'] : null;

            unset($manuscript['review_round']);
            unset($manuscript['suggested_reviewers']);
            unset($manuscript['action_taken']);

            $this->db->table('manuscripts')->insert($manuscript);
            $manuscriptId = $this->db->insertID();

            // Insert initial review round
            $this->db->table('manuscript_rounds')->insert([
                'manuscript_id' => $manuscriptId,
                'round_number' => $round,
                'action_taken' => $action_taken
            ]);

            // Insert reviewer if they had one in the string (mock parsing)
            if ($reviewers) {
                $this->db->table('manuscript_reviewers')->insert([
                    'manuscript_id' => $manuscriptId,
                    'name' => 'Reviewer A',
                    'email' => 'revA@test.com'
                ]);
                $reviewerId = $this->db->insertID();

                $this->db->table('reviewer_rounds')->insert([
                    'reviewer_id' => $reviewerId,
                    'round_number' => $round,
                    'status' => 'Pending Response'
                ]);
            }
        }

        // 3. Seed Sample Notifications
        $notifications = [
            [
                'target_user' => 'Dr. Associate Editor 1',
                'message'     => 'You have been assigned a new manuscript: MJSIR-2026-002',
                'is_read'     => 0,
            ],
            [
                'target_user' => 'Dr. Associate Editor 2',
                'message'     => 'A revision has been submitted for MJSIR-2026-003',
                'is_read'     => 0,
            ],
            [
                'target_user' => 'Chief Editor',
                'message'     => 'Dr. Associate Editor 1 has submitted a recommendation for MJSIR-2026-004',
                'is_read'     => 1,
            ]
        ];

        $this->db->table('notifications')->insertBatch($notifications);
    }
}
