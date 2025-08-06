<?php

namespace App\Models;

use App\Models\Connect;
use App\Models\DB;
use PDO;
use PDOException;

class Module
{
    private DB $db;

    public function __construct()
    {
        $this->db = new DB();
    }

    public function searchModules(string $searchTerm): array
    {
        $sql = "SELECT * FROM modules WHERE name LIKE :searchTerm";
        $params = ['searchTerm' => '%' . $searchTerm . '%'];
        return $this->db->select($sql, $params);
    }
}
