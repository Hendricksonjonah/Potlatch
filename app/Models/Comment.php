<?php

namespace App\Models;

use CodeIgniter\Model;

class Comment extends Model {
    protected $table = 'comment';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';

    protected $allowedFields = ['reply_id', 'item_id', 'user_id', 'comment'];
}

?>