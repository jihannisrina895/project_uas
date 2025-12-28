<?php
/**
 * Google Sheets Integration Class v2
 * Menggunakan Google Apps Script sebagai API
 */

class GoogleSheetsDB {
    private $scriptURL;
    
    public function __construct($scriptURL) {
        $this->scriptURL = $scriptURL;
    }
    
    // Helper function untuk request
    private function makeRequest($data) {
        $ch = curl_init($this->scriptURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        if ($response === false) {
            return ['success' => false, 'error' => 'CURL Error'];
        }
        
        return json_decode($response, true);
    }
    
    // Create/Insert
    public function insert($sheetName, $data) {
        $requestData = [
            'sheet' => $sheetName,
            'action' => 'create',
            'data' => $data
        ];
        
        $result = $this->makeRequest($requestData);
        return isset($result['success']) ? $result['success'] : false;
    }
    
    // Read/Select semua data
    public function getAll($sheetName, $filter = []) {
        $requestData = [
            'sheet' => $sheetName,
            'action' => 'read',
            'filter' => $filter
        ];
        
        $result = $this->makeRequest($requestData);
        
        if (isset($result['success']) && $result['success']) {
            return isset($result['data']) ? $result['data'] : [];
        }
        
        return [];
    }
    
    // Get single record by ID
    public function getById($sheetName, $id) {
        $records = $this->getAll($sheetName, ['id' => $id]);
        return !empty($records) ? $records[0] : null;
    }
    
    // Get single record by email
    public function getByEmail($sheetName, $email) {
        $records = $this->getAll($sheetName, ['email' => $email]);
        return !empty($records) ? $records[0] : null;
    }
    
    // Update
    public function update($sheetName, $id, $data) {
        $requestData = [
            'sheet' => $sheetName,
            'action' => 'update',
            'id' => $id,
            'data' => $data
        ];
        
        $result = $this->makeRequest($requestData);
        return isset($result['success']) ? $result['success'] : false;
    }
    
    // Delete
    public function delete($sheetName, $id) {
        $requestData = [
            'sheet' => $sheetName,
            'action' => 'delete',
            'id' => $id
        ];
        
        $result = $this->makeRequest($requestData);
        return isset($result['success']) ? $result['success'] : false;
    }
}

// Inisialisasi koneksi database menggunakan Apps Script
$db = new GoogleSheetsDB(GOOGLE_SCRIPT_URL);

// Helper functions kompatibilitas dengan kode yang sudah ada
function getProperties($filters = []) {
    global $db;
    return $db->getAll('properties', $filters);
}

function getFeaturedProperties($limit = 3) {
    $properties = getProperties();
    return array_slice($properties, 0, $limit);
}

function getUserByEmail($email) {
    global $db;
    return $db->getByEmail('users', $email);
}

function createUser($userData) {
    global $db;
    return $db->insert('users', $userData);
}

function createProperty($propertyData) {
    global $db;
    return $db->insert('properties', $propertyData);
}

function createPurchase($purchaseData) {
    global $db;
    return $db->insert('transactions', $purchaseData);
}

// Di akhir file spreadsheet.php, tambahkan:

// Fungsi untuk mendapatkan transaksi berdasarkan user_id
function getPurchasesByUserId($user_id) {
    global $db;
    $allTransactions = $db->getAll('transactions');
    $userTransactions = [];
    
    foreach ($allTransactions as $transaction) {
        if (isset($transaction['user_id']) && $transaction['user_id'] == $user_id) {
            $userTransactions[] = $transaction;
        }
    }
    
    return $userTransactions;
}

// Fungsi untuk mendapatkan properti berdasarkan ID
function getPropertyById($property_id) {
    global $db;
    $allProperties = $db->getAll('properties');
    
    foreach ($allProperties as $property) {
        if (isset($property['id']) && $property['id'] == $property_id) {
            return $property;
        }
    }
    
    return null;
}
?>