<?php
include 'db.php';

try {
    // Cast score to integer in SQL and format members
    $stmt = $pdo->query("
        SELECT 
            name, 
            CAST(score AS SIGNED) as score,
            members
        FROM users
    ");

    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Transform data
    $users = array_map(function($user) {
        return [
            'name' => $user['name'],
            'score' => (int)$user['score'],
            'members' => $user['members'] ? explode('.', $user['members']) : []
        ];
    }, $users);

    echo json_encode([
        "success" => true,
        "data" => $users
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Errore nel recupero degli utenti: " . $e->getMessage()
    ]);
}