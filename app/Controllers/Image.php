<?php

namespace App\Controllers;

use Exception;

class Image extends BaseController
{
    // Output image to be used in <img/>
    public function item($potlatch_id, $item_id, $image) {
        helper(['user', 'url']);
        if(isset($this->session->user)){                                   // Check if signed in.
            if(hasAccess($this->session->user->id, $potlatch_id)){         // Check if they have access to the potlatch.
                $imgPath = 'images/'.$potlatch_id.'/'.$item_id.'/'.$image; // Set image file location.
                try{
                    // Get the contents of the file, and convert to an image.
                    $image = imagecreatefromstring(file_get_contents($imgPath));
                    if($image !== false) { // If image creation failed.
                        $this->response->setContentType('image/png');      // Set the type of image to png.
                        imagepng($image, null, -1, -1);                    // Output image as png.
                        imagedestroy($image);                              // Destroy image resource.
                        return;
                    }
                }catch(Exception $e){}
            }
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }else{
            return redirect()->to('/login');
        }
    }
}
?>