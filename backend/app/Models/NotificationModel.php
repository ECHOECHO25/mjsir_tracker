<?php namespace App\Models;
use CodeIgniter\Model;

class NotificationModel extends Model {
    protected $table = 'notifications';
    protected $primaryKey = 'id';
    protected $allowedFields = ['target_user', 'message', 'is_read', 'created_at'];
}
