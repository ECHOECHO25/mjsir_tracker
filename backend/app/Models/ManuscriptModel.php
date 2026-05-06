<?php namespace App\Models;
use CodeIgniter\Model;

class ManuscriptModel extends Model {
    protected $table = 'manuscripts';
    protected $primaryKey = 'id';
    protected $allowedFields = ['code', 'type', 'contributor_type', 'submission_form', 'year', 'date', 'title', 'link', 'inst', 'authors', 'author_email', 'file_path', 'repo', 'editor', 'preAction', 'turnitin', 'aiWriting', 'prenotes', 'revStatus', 'summary', 'eic', 'dret', 'drev', 'dacc', 'criteria_scope', 'criteria_methodology', 'criteria_format', 'manuscript_type', 'turnitin_file', 'turnitin_link', 'editor_comments', 'editor_remarks', 'prescreen_status', 'final_decision', 'date_accepted', 'date_revised'];
}