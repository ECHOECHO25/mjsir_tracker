<?php namespace App\Controllers\Api;
use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;

class Auth extends ResourceController {
    protected $format = 'json';

    public function login() {
        $model = new UserModel();
        $json = $this->request->getJSON();
        
        $email = $json->email ?? '';
        $password = $json->password ?? '';

        if (empty($email) || empty($password)) {
            return $this->failValidationErrors('Email and password are required');
        }

        $user = $model->where('email', $email)->first();

        if (!$user) {
            return $this->failUnauthorized('Invalid credentials');
        }

        // Check lock status
        if ($user['locked_until']) {
            if (strtotime($user['locked_until']) > time()) {
                return $this->failUnauthorized('Account is locked due to too many failed attempts. Try again later.');
            } else {
                // Lock expired, reset
                $model->update($user['id'], ['failed_attempts' => 0, 'locked_until' => null]);
                $user['failed_attempts'] = 0;
            }
        }

        if (password_verify($password, $user['password'])) {
            // Success, reset attempts
            $model->update($user['id'], ['failed_attempts' => 0, 'locked_until' => null]);
            
            // Return user data (without password)
            unset($user['password']);
            return $this->respond(['message' => 'Login successful', 'user' => $user]);
        } else {
            // Failed attempt
            $attempts = $user['failed_attempts'] + 1;
            $lockedUntil = null;
            if ($attempts >= 5) {
                // Lock for 15 minutes (900 seconds)
                $lockedUntil = date('Y-m-d H:i:s', time() + 900);
            }
            $model->update($user['id'], ['failed_attempts' => $attempts, 'locked_until' => $lockedUntil]);
            
            if ($attempts >= 5) {
                return $this->failUnauthorized('Account locked due to too many failed attempts. Try again in 15 minutes.');
            }
            return $this->failUnauthorized('Invalid credentials');
        }
    }
}
