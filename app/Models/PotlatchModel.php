<?php

namespace App\Models;

use CodeIgniter\Model;

class PotlatchModel extends Model {
    protected $table = 'potlatch';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';

    protected $allowedFields = ['user_id'];

}

?>