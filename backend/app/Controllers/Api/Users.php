<?php namespace App\Controllers\Api;
use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;

class Users extends ResourceController {
    protected $modelName = 'App\Models\UserModel';
    protected $format    = 'json';

    public function index() {
        // Return all users except passwords
        $users = $this->model->select('id, name, email, role, created_at')->findAll();
        return $this->respond($users);
    }

    public function create() {
        $json = $this->request->getJSON(true);
        if (empty($json['password'])) {
            return $this->failValidationErrors('Password is required');
        }
        
        $json['password'] = password_hash($json['password'], PASSWORD_DEFAULT);
        
        if ($this->model->insert($json)) {
            return $this->respondCreated(['message' => 'User created']);
        }
        return $this->failServerError('Could not create user');
    }

    public function update($id = null) {
        $json = $this->request->getJSON(true);
        if (!empty($json['password'])) {
            $json['password'] = password_hash($json['password'], PASSWORD_DEFAULT);
        } else {
            unset($json['password']);
        }
        
        if ($this->model->update($id, $json)) {
            return $this->respond(['message' => 'User updated']);
        }
        return $this->failServerError('Could not update user');
    }

    public function delete($id = null) {
        $this->model->delete($id);
        return $this->respondDeleted(['message' => 'User deleted']);
    }
}
