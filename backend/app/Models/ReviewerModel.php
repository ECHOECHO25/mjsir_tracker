<?php namespace App\Models;
use CodeIgniter\Model;

class ReviewerModel extends Model {
    protected $table = 'manuscript_reviewers';
    protected $primaryKey = 'id';
    protected $allowedFields = ['manuscript_id', 'name', 'affiliation', 'email'];
}
