<?php

namespace App\Controllers;

use Exception;

class Potlatch extends BaseController
{
	public function index() {
        if(isset($this->session->user)){
            helper(['form', 'url', 'html']);

            $data['title'] = 'Potlatch';
            $data['user'] = $this->session->user;
            echo view('components/header', $data);
            unset($data);

            // Select all potlatchs that user owns.
            $potlatchModel = new \App\Models\Potlatch();
            $managed = $potlatchModel->where('user_id', $this->session->user->id)->findAll();
            $data['managed'] = $managed;

            // Select potlatchs that user is on the roster for.
            $db = db_connect();
            $joined = $db->query('SELECT potlatch.* FROM roster INNER JOIN potlatch ON roster.potlatch_id = potlatch.id  WHERE roster.user_id = ?',
                [$this->session->user->id])->getResultArray();
            $data['joined'] = $joined;
            echo view('potlatch/potlatchs', $data);

            echo view('components/footer');
        }else{
            return redirect()->to('/login');
        }
	}

    // View individual potlatch and all of it's items.
    public function view($id) {
        if(isset($this->session->user)){ // Check if signed in.
            helper(['form', 'url', 'html', 'user']);
            if(hasAccess($this->session->user->id, $id)){ // Check if the user has access to the potlatch.
                $data['title'] = 'Potlatch';
                $data['user'] = $this->session->user;
                echo view('components/header', $data);
                unset($data);

                // Specify if the user owns th potlatch.
                $data['isOwner'] = isOwner($this->session->user->id, $id);

                // Select the potlatch.
                $potlatchModel = new \App\Models\Potlatch();
                $potlatch = $potlatchModel->where('id', $id)->get()->getRowArray();
                $data['potlatch'] = $potlatch;

                // Select all the potlatch items.
                $potlatchItemModel = new \App\Models\PotlatchItem();
                $potlatchItems = $potlatchItemModel->where('potlatch_id', $id)->get();
                if($potlatchItems){
                    $potlatchItems = $potlatchItems->getResultArray(); // Convert to array for passing to the view.
                    $data['items'] = $potlatchItems;
                }

                // Get everyone that joined the potlatch, if user is the owner.
                if($data['isOwner']){
                    $db = db_connect();
                    $roster = $db->query(
                        'SELECT r.id AS roster_id, r.coins, r.user_id, u.first_name, u.last_name, u.email AS user_email, i.id AS invite_id, i.email AS invite_email
                        FROM roster r LEFT JOIN user u ON u.id = r.user_id
                        LEFT JOIN invite i ON i.roster_id = r.id  WHERE r.potlatch_id = ?', [$id])->getResultArray();
                    $data['roster'] = $roster;
                }
                echo view('potlatch/potlatch', $data);

                echo view('components/footer');
            }else{
                throw new \CodeIgniter\Exceptions\PageNotFoundException();
            }
        }else{
            return redirect()->to('/login');
        }
    }

    // Create potlatch from form data.
    public function createPotlatch() {
        if(isset($this->session->user)){
            $validation = \Config\Services::validation();
            helper(['form', 'url']);

            // Validate post data.
            if(!$this->validate(
                    $validation->getRuleGroup('create_potlatch'),
                    $validation->getRuleGroup('create_potlatch_errors'))){
                // Create error mechanism.
                return redirect()->to('/potlatch/error');
            }else{
                $potlatchModel = new \App\Models\Potlatch();
                // Validate/Sanitize data
                $data = [
                    'user_id' => $this->session->user->id,
                    'title' => $this->request->getVar('title', FILTER_SANITIZE_STRING),
                    'description' => $this->request->getVar('description', FILTER_SANITIZE_STRING)
                ];
                if($potlatchID = $potlatchModel->insert($data)){
                    // Create directory for images.
                    mkdir("images/".$potlatchID, 0777, true);
                    return redirect()->to('/potlatch');
                }else{
                    // Create error mechanism.
                    return redirect()->to('/potlatch/error');
                }
            }
        }else{
            return redirect()->to('/login');
        }
    }

    // Create an item for an individual potlatch.
    public function createItem() {
        if(isset($this->session->user)){ // Check if signed in.
            // Get potlatch id from hidden form input.
            $potlatch_id = $this->request->getVar('potlatch_id', FILTER_VALIDATE_INT);
            helper(['form', 'url', 'user']);
            // Check if potlatch is valid and if the user is the owner.
            if($potlatch_id && isOwner($this->session->user->id, $potlatch_id)){
                $validation = \Config\Services::validation();

                // Validate form data.
                if(!$this->validate(
                        $validation->getRuleGroup('create_potlatch'),
                        $validation->getRuleGroup('create_potlatch_errors'))){
                    return redirect()->to('/potlatch/error');
                }else{
                    $potlatchItemModel = new \App\Models\PotlatchItem();
                    // Validate/Sanitize data
                    $data = [
                        'potlatch_id' => $potlatch_id,
                        'title' => $this->request->getVar('title', FILTER_SANITIZE_STRING),
                        'description' => $this->request->getVar('description', FILTER_SANITIZE_STRING)
                    ];
                    // Insert item.
                    if($item_id = $potlatchItemModel->insert($data)){
                        // Create directory for images.
                        mkdir('images/'.$potlatch_id.'/'.$item_id, 0777, true);
                        // Check if images were provided.
                        if($imagefile = $this->request->getFiles()){
                            // Upload images appropriately.
                            foreach($imagefile['images'] as $img){
                                if ($img->isValid() && !$img->hasMoved())
                                {
                                    $newName = $img->getRandomName();
                                    try{
                                        // Move image to potlatch image folder.
                                        $img->move('images/'.$potlatch_id.'/'.$item_id, $newName);
                                    }catch(Exception $e){
                                        echo $e->getMessage();
                                        exit;
                                    }
                                }
                            }
                        }
                        return redirect()->to('/auction/'.$item_id);
                    }else{
                        return redirect()->to('/potlatch/error');
                    }
                }
            }else{ throw new \CodeIgniter\Exceptions\PageNotFoundException(); }
        }else{ return redirect()->to('/login'); }
    }

    // Create an item for an individual potlatch.
    public function addBidder() {
        if(isset($this->session->user)){ // Check if signed in.
            // Get potlatch id from hidden form input.
            $potlatch_id = $this->request->getVar('potlatch_id', FILTER_VALIDATE_INT); // Should use session to store in future.
            $coins = $this->request->getVar('coins', FILTER_VALIDATE_INT);
            $email = $this->request->getVar('email', FILTER_VALIDATE_EMAIL);
            helper(['form', 'url', 'user', 'text']);
            // Check if potlatch is valid and if the user is the owner.
            if($potlatch_id && $coins && $email && isOwner($this->session->user->id, $potlatch_id)){
                $potlatchRosterModel = new \App\Models\PotlatchRoster();
                $data = [
                    'potlatch_id' => $potlatch_id,
                    'coins' => $coins
                ];
                // Create new roster slot.
                if($id = $potlatchRosterModel->insert($data)){
                    $rosterInviteModel = new \App\Models\RosterInvite();
                    $randomId = random_string('crypto', 32); // Generate roster invite code.
                    $data = [
                        'id' => $randomId,
                        'roster_id' => $id,
                        'email' => $email
                    ];
                    if($rosterInviteModel->insert($data, false)){
                        return redirect()->to('/potlatch/'.$potlatch_id);
                    }else{
                        echo 'Failed to instert invite.';
                        var_dump($data);
                        exit;
                    }
                }else{ throw new \CodeIgniter\Exceptions\PageNotFoundException(); }
            }else{ throw new \CodeIgniter\Exceptions\PageNotFoundException(); }
        }else{ return redirect()->to('/login'); }
    }

    public function joinPotlatch() {
        if(isset($this->session->user)){                                            // Check if signed in.
            helper('user');                                                         // Load user helper.
            // Get invite from database using provided code.
            $inviteCode = $this->request->getVar('code', FILTER_SANITIZE_STRING);
            $rosterInviteModel = new \App\Models\RosterInvite();
            $rosterInvite = $rosterInviteModel->where('id', $inviteCode)->get()->getRow();
            if($rosterInvite){                                                      // Check if invite is valid.
                // Get roster row from database using invite roster_id.
                $potlatchRosterModel = new \App\Models\PotlatchRoster();
                $roster = $potlatchRosterModel->where('id', $rosterInvite->roster_id);
                if($roster){                                                        // Check if roster is valid.
                    if(!isOwner($this->session->user->id, $roster->potlatch_id)){   // Check if user is not the owner.
                        // Update roster with the users id.
                        if($potlatchRosterModel->update($roster->id, ['user_id' => $this->session->user->id])){
                            $rosterInviteModel->where('id', $inviteCode)->delete(); // Delete the invite.
                            return redirect()->to('/potlatch/'.$roster->potlatch_id);
                        }else{ throw new \CodeIgniter\Exceptions\PageNotFoundException(); }
                    }else{ throw new \CodeIgniter\Exceptions\PageNotFoundException(); }
                }else{ throw new \CodeIgniter\Exceptions\PageNotFoundException(); }
            }else{ throw new \CodeIgniter\Exceptions\PageNotFoundException(); }
        }else{ return redirect()->to('/login'); }
    }
}

?>