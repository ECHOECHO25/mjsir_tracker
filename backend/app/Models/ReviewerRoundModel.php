<?php namespace App\Models;
use CodeIgniter\Model;

class ReviewerRoundModel extends Model {
    protected $table = 'reviewer_rounds';
    protected $primaryKey = 'id';
    protected $allowedFields = ['reviewer_id', 'round_number', 'status', 'date_sent', 'sent_file_path', 'sent_file_link', 'file_path', 'file_link'];
}
