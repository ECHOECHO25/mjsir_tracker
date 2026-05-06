<?php namespace App\Models;
use CodeIgniter\Model;

class ReviewRoundModel extends Model {
    protected $table = 'manuscript_rounds';
    protected $primaryKey = 'id';
    protected $allowedFields = ['manuscript_id', 'round_number', 'revised_file_path', 'revised_link', 'action_taken_file_path', 'action_taken_link', 'action_taken'];
}
