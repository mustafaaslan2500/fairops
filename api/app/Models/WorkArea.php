<?php

namespace App\Models;

use App\Models\Connect;
use App\Models\DB;
use PDO;
use PDOException;

class WorkArea
{
    private DB $db;

    public function __construct()
    {
        $this->db = new DB();
    }

    public function createArea(array $data): bool|string
    {
        $modulesJson = json_encode($data['modules']);
        if ($modulesJson === false) {
            return false;
        }

        $sql = "INSERT INTO company_info (name, address, number, domain, db_name, modules, created_at)
                VALUES (:name, :address, :phone, :domain, :db_name, :modules, NOW())";

        return $this->db->insert($sql, [
            'name' => $data['name'],
            'address' => $data['address'],
            'phone' => $data['phone'],
            'domain' => $data['domain'],
            'db_name' => $data['db_name'],
            'modules' => $modulesJson
        ]);
    }

    public function domainControl(string $domainName): int
    {
        $sql = "SELECT COUNT(*) as count FROM company_info WHERE domain = :domainName";
        $result = $this->db->selectOne($sql, ['domainName' => $domainName]);
        return $result['count'] ?? 0;
    }

    public function createCompanyDB($companyName, $dbName)
    {
        try {
            $pdo = Connect::initialize();

            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
            $pdo->exec("USE `$dbName`;");

            $tables =
                "CREATE TABLE `accessible_modules` (
                `id` VARCHAR(50) NOT NULL DEFAULT '0' COLLATE 'utf8mb4_general_ci',
                `name` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `icon` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                `sort` INT NULL DEFAULT '0',
                PRIMARY KEY (`id`) USING BTREE
            )
            COLLATE='utf8mb4_general_ci'
            ENGINE=InnoDB;

                CREATE TABLE `settings` (
                    `name` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                    `value` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
                    `show` INT NULL DEFAULT '0'
                )
            COLLATE='utf8mb4_general_ci'
            ENGINE=InnoDB
            ;

                CREATE TABLE `users` (
                `u_id` INT NOT NULL AUTO_INCREMENT,
                `u_first_name` VARCHAR(200) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
                `u_last_name` VARCHAR(200) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
                `u_email` VARCHAR(350) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
                `u_password` TEXT NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
                `u_user_info` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_0900_ai_ci',
                `u_phone_number` VARCHAR(10) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_ai_ci',
                `u_profile_photo` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_0900_ai_ci',
                `u_last_active_date` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_ai_ci',
                `u_active` INT NOT NULL DEFAULT '1',
                `u_confirm_code` VARCHAR(10) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_ai_ci',
                `u_confirm_code_date` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_ai_ci',
                `u_register_date` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_ai_ci',
                `u_contanct_permission` INT NOT NULL DEFAULT '0',
                `u_admin` INT NULL DEFAULT '0',
                `u_lang` VARCHAR(10) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_ai_ci',
                `u_current_country` INT NULL DEFAULT NULL,
                `u_current_city` INT NULL DEFAULT NULL,
                `u_current_district` INT NULL DEFAULT NULL,
                `allowed_modules` JSON NULL DEFAULT NULL,
                PRIMARY KEY (`u_id`) USING BTREE,
                UNIQUE INDEX `u_email` (`u_email`) USING BTREE
            )
            COLLATE='utf8mb4_0900_ai_ci'
            ENGINE=InnoDB
            ;

                CREATE TABLE `user_tokens` (
                `ut_id` INT NOT NULL AUTO_INCREMENT,
                `ut_user_id` INT NOT NULL,
                `ut_token` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_0900_ai_ci',
                `ut_ip` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_ai_ci',
                `ut_register_date` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_ai_ci',
                `ut_last_ip` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_ai_ci',
                PRIMARY KEY (`ut_id`) USING BTREE,
                INDEX `ut_user_id` (`ut_user_id`) USING BTREE
            )
            COLLATE='utf8mb4_0900_ai_ci'
            ENGINE=InnoDB
            AUTO_INCREMENT=33;

            #max_users->5 insert in the settings

            INSERT INTO `settings` (`name`, `value`, `show`) VALUES
            ('max_users', '5', 1),
            ('company_name', '$companyName', 1),
            ('company_domain', '$dbName', 0),
            ('company_db_name', '$dbName', 0),
            ('company_logo', '', 1);
            ";


            $pdo->exec($tables);

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function setAdminUser($adminUsers, $compDB = null)
    {
        if (!$compDB) {
            return false;
        }
        $pdo = Connect::initialize($compDB);
        $userPass = password_hash($adminUsers['password'], PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (u_first_name, u_last_name, u_email, u_password, u_register_date, u_admin)
                VALUES (:first_name, :last_name, :email, :password, NOW(), 1)";

        return $pdo->prepare($sql)->execute([
            'first_name' => $adminUsers['first_name'],
            'last_name' => $adminUsers['last_name'],
            'email' => $adminUsers['email'],
            'password' => $userPass
        ]) !== false;
    }

    public function addUserToWorkArea($companyId, $user)
    {
        $row = $this->db->selectOne(
            "SELECT db_name FROM company_info WHERE id = :id",
            ['id' => $companyId]
        );
        if (!$row) return false;

        $compDB = Connect::initialize($row['db_name']);

        $settings = $compDB->prepare("
            SELECT 
        CASE 
            WHEN (SELECT COUNT(*) FROM users WHERE u_active = '1') >= 
                (SELECT value FROM settings WHERE name = 'max_users') 
            THEN FALSE 
            ELSE TRUE 
        END AS result;
        ");
        $settings->execute();
        $settings = $settings->fetch(PDO::FETCH_ASSOC);
        if ($settings['result'] != 1) {
            return 'user_limit_exceeded';
        }

        $checkSql = "SELECT COUNT(*) FROM users WHERE u_email = :email";
        $stmt = $compDB->prepare($checkSql);
        $stmt->execute(['email' => $user['email']]);
        if ($stmt->fetchColumn() > 0) {
            return 'already_exists';
        }

        $userPass = password_hash($user['password'], PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (u_first_name, u_last_name, u_email, u_password, u_register_date, u_admin)
            VALUES (:first_name, :last_name, :email, :password, NOW(), :is_admin)";

        return $compDB->prepare($sql)->execute([
            'first_name' => $user['first_name'],
            'last_name'  => $user['last_name'],
            'email'      => $user['email'],
            'password'   => $userPass,
            'is_admin'   => $user['is_admin'] ? 1 : 0
        ]) !== false;
    }

    public function editWorkAreaUser($companyId, $userId, $user)
    {
        $row = $this->db->selectOne(
            "SELECT db_name FROM company_info WHERE id = :id",
            ['id' => $companyId]
        );
        if (!$row) return false;

        $compDB = Connect::initialize($row['db_name']);

        if (!empty($user['password'])) {
            $userPass = password_hash($user['password'], PASSWORD_DEFAULT);
        } else {
            $userPass = null; // Şifre boş bırakılırsa güncellenmez
        }

        $sql = "UPDATE users SET u_first_name = :first_name, u_last_name = :last_name, 
                u_email = :email, " . (!is_null($userPass) ? "u_password = :password, " : "") . " u_admin = :is_admin WHERE u_id = :user_id";

        $data = [
            'first_name' => $user['first_name'],
            'last_name'  => $user['last_name'],
            'email'      => $user['email'],
            'is_admin'   => $user['is_admin'] ? 1 : 0,
            'user_id'    => $userId
        ];

        if (!is_null($userPass)) {
            $data['password'] = $userPass;
        }

        return $compDB->prepare($sql)->execute($data) !== false;
    }

    public function removeWorkAreaUser($companyId, $userId)
    {
        $row = $this->db->selectOne(
            "SELECT db_name FROM company_info WHERE id = :id",
            ['id' => $companyId]
        );
        if (!$row) return false;

        $compDB = Connect::initialize($row['db_name']);

        $sql = "UPDATE users SET u_active = 0 WHERE u_id = :user_id";
        return $compDB->prepare($sql)->execute(['user_id' => $userId]) !== false;
    }

    public function removeAdminAccess($userIds)
    {
        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $sql = "UPDATE users SET u_admin = 0 WHERE u_id IN ($placeholders)";
        return $this->db->update($sql, $userIds) > 0;
    }

    public function getModules($modulesIds = [], $getAllColumns = false)
    {
        if (empty($modulesIds)) return [];

        $sql = "SELECT " . ($getAllColumns ? "*" : "id,name") . " FROM modules";

        if (!empty($modulesIds)) {
            $placeholders = implode(',', array_fill(0, count($modulesIds), '?'));
            $sql .= " WHERE id IN ($placeholders)";
            return $this->db->select($sql, $modulesIds);
        }

        return $this->db->select($sql);
    }

    public function setModules($modules, $companyId = null, $dbConnection = null)
    {
        if ($companyId) {
            $dbInfo = $this->db->selectOne("SELECT db_name FROM company_info WHERE id = :id", ['id' => $companyId]);
            if (!$dbInfo) return false;
            $pdo = Connect::initialize($dbInfo['db_name']);
        } else {
            $pdo = $dbConnection ?: Connect::initialize();
        }

        $sql = "INSERT INTO accessible_modules (id, name) VALUES (:id, :name)";
        $stmt = $pdo->prepare($sql);

        foreach ($modules as $module) {
            $stmt->execute(['id' => $module['id'], 'name' => $module['name']]);
        }

        return true;
    }

    public function removeModules($modules, $companyId = null, $dbConnection = null)
    {
        if (empty($modules)) return true;

        if ($companyId) {
            $dbInfo = $this->db->selectOne("SELECT db_name FROM company_info WHERE id = :id", ['id' => $companyId]);
            if (!$dbInfo) return false;
            $pdo = Connect::initialize($dbInfo['db_name']);
        } else {
            $pdo = $dbConnection ?: Connect::initialize();
        }

        $placeholders = implode(',', array_fill(0, count($modules), '?'));
        $sql = "DELETE FROM accessible_modules WHERE id IN ($placeholders)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($modules);

        return true;
    }

    public function removeWorkArea($companyId)
    {
        $sql = "UPDATE company_info SET active = 0 WHERE id = :id";
        return $this->db->update($sql, ['id' => $companyId]) > 0;
    }

    public function listWorkAreas($page = 1, $limit = 20, $search_term = null)
    {
        if ($search_term) {
            $sql = "SELECT * FROM company_info WHERE active = 1 AND (name LIKE :search OR address LIKE :search_two OR number LIKE :search_there) ORDER BY id DESC LIMIT :offset, :limit";
            $params = [
                'search' => '%' . $search_term . '%',
                'search_two' => '%' . $search_term . '%',
                'search_there' => '%' . $search_term . '%',
                'offset' => ($page - 1) * $limit,
                'limit' => $limit
            ];
        } else {
            $sql = "SELECT * FROM company_info WHERE active = 1 ORDER BY id DESC LIMIT :offset, :limit";
            $params = [
                'offset' => ($page - 1) * $limit,
                'limit' => $limit
            ];
        }

        return $this->db->select($sql, $params);
    }

    public function getWorkAreaCount($search_term = null)
    {
        if ($search_term) {
            $sql = "SELECT COUNT(*) as count FROM company_info WHERE active = 1 AND (name LIKE :search OR address LIKE :search_two OR number LIKE :search_there)";
            $params = [
                'search' => '%' . $search_term . '%',
                'search_two' => '%' . $search_term . '%',
                'search_there' => '%' . $search_term . '%'
            ];
        } else {
            $sql = "SELECT COUNT(*) as count FROM company_info WHERE active = 1";
            $params = [];
        }

        $result = $this->db->selectOne($sql, $params);
        return $result['count'] ?? 0;
    }

    public function getWorkAreaInfo($companyId)
    {
        return $this->db->selectOne("SELECT * FROM company_info WHERE id = :id AND active = 1", ['id' => $companyId]);
    }

    public function editWorkArea($data, $addModules)
    {
        // Güncellenecek alanları dinamik oluştur
        $fields = [];
        $params = ['id' => $data['company_id']];

        if (!empty($data['company_name'])) {
            $fields[] = "name = :name";
            $params['name'] = $data['company_name'];
        }

        if (!empty($data['company_address'])) {
            $fields[] = "address = :address";
            $params['address'] = $data['company_address'];
        }

        if (!empty($data['company_number'])) {
            $fields[] = "number = :phone";
            $params['phone'] = $data['company_number'];
        }

        // Eğer güncellenecek alan varsa UPDATE çalıştır
        if (!empty($fields)) {
            $sql = "UPDATE company_info SET " . implode(', ', $fields) . " WHERE id = :id";
            $this->db->update($sql, $params);
        }

        // Firma veritabanı bağlantısı
        $row = $this->db->selectOne("SELECT db_name FROM company_info WHERE id = :id", ['id' => $data['company_id']]);
        if (!$row) return false;

        $compDB = Connect::initialize($row['db_name']);

        // Modül silme ve ekleme
        $this->removeModules($data['removed_modules'], null, $compDB);
        $this->setModules($addModules, null, $compDB);

        // Firma adı güncellemesi (sadece company_name varsa)
        if (!empty($data['company_name'])) {
            $stmt = $compDB->prepare("UPDATE settings SET value = :name WHERE name = 'company_name'");
            $stmt->execute(['name' => $data['company_name']]);
        }

        return [
            'company_id' => $data['company_id'],
            'removed' => true,
            'added' => true,
            'new_name' => $data['company_name'] ?? null
        ];
    }


    public function getWorkAreaUsers($compDB)
    {
        $sql = "SELECT u_id, u_first_name, u_last_name, u_email, u_phone_number, u_profile_photo,  u_admin
                FROM users WHERE u_active = 1";
        $stmt = $compDB->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRootModules($compDB)
    {
        $sql = "SELECT id, name, icon FROM accessible_modules";
        $stmt = $compDB->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getWorkAreaPaymentHistory($companyId)
    {
        $sql = "SELECT id,amount,status,date FROM payment_history WHERE company_id = :companyId";
        return $this->db->select($sql, ['companyId' => $companyId]);
    }

    public function getWorkAreaSettings($compDB)
    {
        $sql = "SELECT `name`, `value` FROM settings WHERE `show` = 1";
        $stmt = $compDB->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getWorkAreaDetail($companyId)
    {
        $row = $this->db->selectOne("SELECT db_name FROM company_info WHERE id = :id", ['id' => $companyId]);
        if (!$row) return false;
        $compDB = Connect::initialize($row['db_name']);

        $data = [
            'company_info' => $this->getWorkAreaInfo($companyId),
            'company_users' => $this->getWorkAreaUsers($compDB),
            'modules' => $this->getModules(array_column($this->getRootModules($compDB), 'id'), true),
            'payment_history' => $this->getWorkAreaPaymentHistory($companyId),
            'company_settings' => $this->getWorkAreaSettings($compDB)
        ];

        return $data;
    }
}
