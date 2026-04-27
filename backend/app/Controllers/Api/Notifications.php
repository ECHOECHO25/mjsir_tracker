<?php namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\NotificationModel;

class Notifications extends ResourceController {
    public function getByUser() {
        $target = $this->request->getVar('target_user');
        $role = $this->request->getVar('role');
        
        if (!$target && !$role) {
            return $this->fail('Target user or role required');
        }

        $model = new NotificationModel();
        
        $builder = $model->builder();
        $builder->groupStart()
                ->where('target_user', $target)
                ->orWhere('target_user', $role)
                ->orWhere('target_user', 'all')
                ->groupEnd();
        
        $builder->orderBy('created_at', 'DESC');
        $builder->limit(50);
        
        $query = $builder->get();
        $notifications = $query->getResultArray();

        return $this->respond($notifications);
    }

    public function markAsRead($id = null) {
        if (!$id) return $this->fail('ID required');
        
        $model = new NotificationModel();
        $model->update($id, ['is_read' => 1]);
        
        return $this->respond(['status' => 'success']);
    }

    public function createNotification() {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();
        
        if (empty($data['target_user']) || empty($data['message'])) {
            return $this->fail('Target user and message are required');
        }

        $model = new NotificationModel();
        $model->insert([
            'target_user' => $data['target_user'],
            'message' => $data['message'],
            'is_read' => 0
        ]);

        return $this->respondCreated(['status' => 'success']);
    }
}
