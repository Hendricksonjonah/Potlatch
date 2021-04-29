<?php

namespace App\Controllers;

use Exception;

class Auction extends BaseController
{
    // Shows an item's auction based on the id.
    public function view($id) {
        if(isset($this->session->user)){ // If signed in.
            helper(['form', 'url', 'html', 'user']);

            // Get the item from database.
            $potlatchItemModel = new \App\Models\PotlatchItem();
            $potlatchItem = $potlatchItemModel->where('id', $id)->get()->getRow();

            // If item exists, and user has access.
            if($potlatchItem && hasAccess($this->session->user->id, $potlatchItem->potlatch_id)){
                $data['title'] = 'Auction';
                $data['user'] = $this->session->user;
                echo view('components/header', $data);
                unset($data);

                $itemBidModel = new \App\Models\ItemBid();

                // Get's row with highest amount. But only returns the amount...
                $highestBid = $itemBidModel->where('item_id', $id)->selectMax('amount')->get()->getRow();
                // So we use the item_id and the amount to get the proper row with all the information.
                $highestBid = $itemBidModel->where(['item_id' => $id, 'amount' => $highestBid->amount])->get()->getRow();

                // Setting data to be passed to the view.
                $data['item'] = (array)$potlatchItem;
                $data['highestBid'] = (array)$highestBid;
                $data['isOwner'] = isOwner($this->session->user->id, $potlatchItem->potlatch_id); // Check if isOwner of potlatch.
                $data['isHighestBidder'] = $highestBid->user_id == $this->session->user->id;      // Check if highest bidder.
                $data['canBid'] = (!$data['isOwner'] &&                                           // Check whether can bid.
                                   !$data['isHighestBidder'] &&
                                   $highestBid->amount+1 <= getAvailableCoins($this->session->user->id, $potlatchItem->potlatch_id));
                // Get list of all images in item folder.
                try{
                    // Get all files/directories in folder images/potlatch_id/item_id
                    $files = scandir('images/'.$potlatchItem->potlatch_id.'/'.$potlatchItem->id);
                    foreach($files as $file) {
                        // Check if not a file.
                        if($file == '.' || $file == '..') continue;
                        $data['images'][] = $file;
                    }
                }catch(Exception $e){}
                echo view('potlatch/auction', $data);

                echo view('components/footer');
            }else{
                throw new \CodeIgniter\Exceptions\PageNotFoundException();
            }
        }else{
            return redirect()->to('/login');
        }
    }

    public function bid(){
        if(isset($this->session->user)){ // If signed in.
            helper(['url', 'user']);
            // Get hidden inputs.
            $item_id = $this->request->getVar('item_id', FILTER_VALIDATE_INT);
            $bidInput = $this->request->getVar('bid', FILTER_VALIDATE_INT);
            $hBidInput = $this->request->getVar('highestBid', FILTER_VALIDATE_INT);
            // Get potlatch item info.
            $potlatchItemModel = new \App\Models\PotlatchItem();
            $potlatchItem = $potlatchItemModel->where('id', $item_id)->get()->getRow();
            // If item exists, and user has access, and is not the owner.
            if($potlatchItem &&
                    hasAccess($this->session->user->id, $potlatchItem->potlatch_id) &&
                    !isOwner($this->session->user->id, $potlatchItem->potlatch_id)){
                $itemBidModel = new \App\Models\ItemBid();
                // Get highest bid for the item.
                $hBid = $itemBidModel->where('item_id', $item_id)->selectMax('amount')->get()->getRow();
                $hBid = $itemBidModel->where(['item_id' => $item_id, 'amount' => $hBid->amount])->get()->getRow();
                // Get the amount of available coins to spend.
                $aCoins = getAvailableCoins($this->session->user->id, $potlatchItem->potlatch_id);
                // If there's not bids or If there's inputted bid is higher the highest bid.
                if(($hBidInput == false && is_null($hBid)) || ($bidInput != false && !is_null($hBid) && $hBid->user_id != $this->session->user->id && $bidInput >= $hBid->amount)){
                    // If user has enough coins, and isn't last bidder.
                    if(isset($aCoins)){
                        // No one has bid and user has one coin.
                        if(is_null($hBid) && $aCoins >= 1){
                            $amount = 1;
                        // Someone has bid, and user has enough coins to bid inputted amount.
                        }else if(!is_null($hBid) && $bidInput <= $aCoins){
                            $amount = $bidInput;
                        }else{
                            echo 'Not enough coins';
                            exit;
                        }
                        $data = [
                            'item_id' => $item_id,
                            'user_id' => $this->session->user->id,
                            'amount' => $amount
                        ];
                        // Insert bid.
                        if($itemBidModel->insert($data)){
                            return redirect()->to('/auction/'.$item_id);
                        }else{
                            echo 'Failed to insert<hr>';
                            var_dump($data);
                            echo '<hr>';
                            var_dump($hBid);
                        }
                    }else{
                        echo 'Failed to get available coins.';
                        exit;
                    }
                }else{
                    echo 'Someone has already placed a higher bid.';
                    exit;
                }
            }else{
                throw new \CodeIgniter\Exceptions\PageNotFoundException();
            }
        }else{
            return redirect()->to('/login');
        }
    }
}

?>