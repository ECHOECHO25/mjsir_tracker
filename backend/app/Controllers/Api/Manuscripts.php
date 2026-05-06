<?php namespace App\Controllers\Api;
use CodeIgniter\RESTful\ResourceController;

class Manuscripts extends ResourceController {
    protected $modelName = 'App\Models\ManuscriptModel';
    protected $format    = 'json';

    public function index() { 
        $manuscripts = $this->model->orderBy('id', 'DESC')->findAll();
        $reviewerModel = new \App\Models\ReviewerModel();
        $revRoundModel = new \App\Models\ReviewerRoundModel();
        $roundModel = new \App\Models\ReviewRoundModel();
        
        foreach ($manuscripts as &$m) {
            $m['suggested_reviewers'] = $reviewerModel->where('manuscript_id', $m['id'])->findAll();
            foreach ($m['suggested_reviewers'] as &$rev) {
                $rev['reviewer_rounds'] = $revRoundModel->where('reviewer_id', $rev['id'])->findAll();
            }
            $m['review_rounds'] = $roundModel->where('manuscript_id', $m['id'])->findAll();
        }
        
        return $this->respond($manuscripts);
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
        if ($file && $file->isValid() && !$file->hasMoved()) {
            if (!is_dir(FCPATH . 'uploads')) mkdir(FCPATH . 'uploads', 0777, true);
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads', $newName);
            $data['file_path'] = 'uploads/' . $newName;
        }

        $turnitinFile = $this->request->getFile('turnitin_file');
        if ($turnitinFile && $turnitinFile->isValid() && !$turnitinFile->hasMoved()) {
            if (!is_dir(FCPATH . 'uploads')) mkdir(FCPATH . 'uploads', 0777, true);
            $newName = $turnitinFile->getRandomName();
            $turnitinFile->move(FCPATH . 'uploads', $newName);
            $data['turnitin_file'] = 'uploads/' . $newName;
        }

        return $data;
    }

    private function saveRelations($manuscriptId, $data) {
        $reviewerModel = new \App\Models\ReviewerModel();
        $revRoundModel = new \App\Models\ReviewerRoundModel();
        $roundModel = new \App\Models\ReviewRoundModel();

        if (isset($data['suggested_reviewers'])) {
            // Get all existing reviewers for this manuscript to delete their rounds first
            $existingRevs = $reviewerModel->where('manuscript_id', $manuscriptId)->findAll();
            foreach ($existingRevs as $er) {
                $revRoundModel->where('reviewer_id', $er['id'])->delete();
            }
            $reviewerModel->where('manuscript_id', $manuscriptId)->delete();
            
            $reviewers = json_decode($data['suggested_reviewers'], true);
            if (is_array($reviewers)) {
                foreach ($reviewers as $idx => $rev) {
                    $reviewerModel->insert([
                        'manuscript_id' => $manuscriptId,
                        'name' => $rev['name'] ?? null,
                        'affiliation' => $rev['affiliation'] ?? $rev['affil'] ?? null,
                        'email' => $rev['email'] ?? null,
                    ]);
                    $reviewerId = $reviewerModel->getInsertID();

                    if (isset($rev['reviewer_rounds']) && is_array($rev['reviewer_rounds'])) {
                        foreach ($rev['reviewer_rounds'] as $rIdx => $rRound) {
                            $file = $this->request->getFile("reviewer_file_{$idx}_{$rIdx}");
                            if ($file && $file->isValid() && !$file->hasMoved()) {
                                if (!is_dir(FCPATH . 'uploads')) mkdir(FCPATH . 'uploads', 0777, true);
                                $newName = $file->getRandomName();
                                $file->move(FCPATH . 'uploads', $newName);
                                $rRound['file_path'] = 'uploads/' . $newName;
                            }

                            $sentFile = $this->request->getFile("reviewer_sent_file_{$idx}_{$rIdx}");
                            if ($sentFile && $sentFile->isValid() && !$sentFile->hasMoved()) {
                                if (!is_dir(FCPATH . 'uploads')) mkdir(FCPATH . 'uploads', 0777, true);
                                $newName = $sentFile->getRandomName();
                                $sentFile->move(FCPATH . 'uploads', $newName);
                                $rRound['sent_file_path'] = 'uploads/' . $newName;
                            }
                            
                            $revRoundModel->insert([
                                'reviewer_id' => $reviewerId,
                                'round_number' => $rRound['round_number'] ?? ($rIdx + 1),
                                'status' => $rRound['status'] ?? null,
                                'date_sent' => $rRound['date_sent'] ?? null,
                                'sent_file_path' => $rRound['sent_file_path'] ?? null,
                                'sent_file_link' => $rRound['sent_file_link'] ?? null,
                                'file_path' => $rRound['file_path'] ?? null,
                                'file_link' => $rRound['file_link'] ?? null,
                            ]);
                        }
                    }
                }
            }
        }

        if (isset($data['review_rounds'])) {
            $roundModel->where('manuscript_id', $manuscriptId)->delete();
            $rounds = json_decode($data['review_rounds'], true);
            if (is_array($rounds)) {
                foreach ($rounds as $idx => $round) {
                    $revFile = $this->request->getFile("round_revised_file_$idx");
                    if ($revFile && $revFile->isValid() && !$revFile->hasMoved()) {
                        if (!is_dir(FCPATH . 'uploads')) mkdir(FCPATH . 'uploads', 0777, true);
                        $newName = $revFile->getRandomName();
                        $revFile->move(FCPATH . 'uploads', $newName);
                        $round['revised_file_path'] = 'uploads/' . $newName;
                    }
                    
                    $actFile = $this->request->getFile("round_action_file_$idx");
                    if ($actFile && $actFile->isValid() && !$actFile->hasMoved()) {
                        if (!is_dir(FCPATH . 'uploads')) mkdir(FCPATH . 'uploads', 0777, true);
                        $newName = $actFile->getRandomName();
                        $actFile->move(FCPATH . 'uploads', $newName);
                        $round['action_taken_file_path'] = 'uploads/' . $newName;
                    }

                    $roundModel->insert([
                        'manuscript_id' => $manuscriptId,
                        'round_number' => $round['round_number'] ?? ($idx + 1),
                        'revised_file_path' => $round['revised_file_path'] ?? null,
                        'revised_link' => $round['revised_link'] ?? null,
                        'action_taken_file_path' => $round['action_taken_file_path'] ?? null,
                        'action_taken_link' => $round['action_taken_link'] ?? null,
                        'action_taken' => $round['action_taken'] ?? null,
                    ]);
                }
            }
        }
    }

    public function create() { 
        $data = $this->getRequestData();
        // Remove relation strings before model insert to avoid field errors if we used strict allowedFields
        $insertData = $data;
        unset($insertData['suggested_reviewers']);
        unset($insertData['review_rounds']);

        $id = $this->model->insert($insertData, true); 
        if ($id) {
            $this->saveRelations($id, $data);
        }
        return $this->respondCreated(); 
    }

    public function update($id = null) { 
        $data = $this->getRequestData();
        $updateData = $data;
        unset($updateData['suggested_reviewers']);
        unset($updateData['review_rounds']);

        $this->model->update($id, $updateData); 
        $this->saveRelations($id, $data);
        return $this->respondUpdated(); 
    }
    
    public function updatePost($id = null) {
        $data = $this->getRequestData();
        $updateData = $data;
        unset($updateData['suggested_reviewers']);
        unset($updateData['review_rounds']);

        $this->model->update($id, $updateData); 
        $this->saveRelations($id, $data);
        return $this->respondUpdated(); 
    }

    public function delete($id = null) { 
        $this->model->delete($id); 
        return $this->respondDeleted(); 
    }
}