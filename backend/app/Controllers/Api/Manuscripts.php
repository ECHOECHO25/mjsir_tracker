<?php namespace App\Controllers\Api;
use CodeIgniter\RESTful\ResourceController;

class Manuscripts extends ResourceController {
    protected $modelName = 'App\Models\ManuscriptModel';
    protected $format    = 'json';

    public function index() { 
        return $this->respond($this->model->orderBy('id', 'DESC')->findAll()); 
    }

    private function getRequestData() {
        $data = [];
        $contentType = $this->request->getHeaderLine('Content-Type');
        if (strpos($contentType, 'application/json') !== false) {
            $data = $this->request->getJSON(true);
        } else {
            $data = $this->request->getPost();
        }
        
        $file = $this->request->getFile('upload_file');
        // Handle revised_file upload
        $revisedFile = $this->request->getFile('revised_file');
        if ($revisedFile && $revisedFile->isValid() && !$revisedFile->hasMoved()) {
            if (!is_dir(FCPATH . 'uploads')) {
                mkdir(FCPATH . 'uploads', 0777, true);
            }
            $newName = $revisedFile->getRandomName();
            $revisedFile->move(FCPATH . 'uploads', $newName);
            $data['revised_file'] = 'uploads/' . $newName;
        }

        if ($file && $file->isValid() && !$file->hasMoved()) {
            if (!is_dir(FCPATH . 'uploads')) {
                mkdir(FCPATH . 'uploads', 0777, true);
            }
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads', $newName);
            $data['file_path'] = 'uploads/' . $newName;
        }

        $turnitinFile = $this->request->getFile('turnitin_file');
        if ($turnitinFile && $turnitinFile->isValid() && !$turnitinFile->hasMoved()) {
            if (!is_dir(FCPATH . 'uploads')) {
                mkdir(FCPATH . 'uploads', 0777, true);
            }
            $newName = $turnitinFile->getRandomName();
            $turnitinFile->move(FCPATH . 'uploads', $newName);
            $data['turnitin_file'] = 'uploads/' . $newName;
        }

        // Clean up empty strings to null or leave them (depending on DB)
        // Ensure no empty arrays are passed if fields are empty
        return $data;
    }

    public function create() { 
        $data = $this->getRequestData();
        $this->model->insert($data); 
        return $this->respondCreated(); 
    }

    public function update($id = null) { 
        // When updating via FormData, browsers often use POST with a _method=PUT or we can just accept POST for updates in our custom route.
        // But if Vue sends PUT with FormData, PHP might not parse it easily. 
        // Best approach for FormData is to send POST with id, or we just handle it here.
        $data = $this->getRequestData();
        $this->model->update($id, $data); 
        return $this->respondUpdated(); 
    }
    
    // Fallback for FormData put requests
    public function updatePost($id = null) {
        $data = $this->getRequestData();
        $this->model->update($id, $data); 
        return $this->respondUpdated(); 
    }

    public function delete($id = null) { 
        $this->model->delete($id); 
        return $this->respondDeleted(); 
    }
}