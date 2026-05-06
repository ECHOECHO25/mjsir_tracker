<?php namespace App\Models;
use CodeIgniter\Model;

class ManuscriptModel extends Model {
    protected $table = 'manuscripts';
    protected $primaryKey = 'id';
    protected $allowedFields = ['code', 'type', 'contributor_type', 'submission_form', 'year', 'date', 'title', 'link', 'inst', 'authors', 'author_email', 'file_path', 'repo', 'editor', 'preAction', 'turnitin', 'aiWriting', 'prenotes', 'srev', 'srev1_affil', 'srev1_email', 'srev2_name', 'srev2_affil', 'srev2_email', 'srev3_name', 'srev3_affil', 'srev3_email', 'revStatus', 'summary', 'rev1', 'rev2', 'rev3', 'eic', 'dret', 'drev', 'dacc', 'criteria_scope', 'criteria_methodology', 'criteria_format', 'manuscript_type', 'turnitin_file', 'turnitin_link', 'revised_file', 'revised_link', 'revised_file_2', 'revised_link_2', 'revised_file_3', 'revised_link_3', 'action_taken_file', 'action_taken_link', 'editor_comments', 'editor_remarks', 'prescreen_status', 'action_taken', 'suggested_reviewers', 'final_decision', 'date_accepted', 'date_revised', 'review_round'];
}