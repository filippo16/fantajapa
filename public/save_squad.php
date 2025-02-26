<?php
require 'db.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['squad']) || !is_array($data['squad']) || !isset($data['name'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dati non validi']);
    exit;
}

$squadString = implode('.', $data['squad']);

// Prepara e esegui la query di inserimento
$query = "UPDATE users SET members = :squad WHERE name = :name";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':squad', $squadString);
$stmt->bindParam(':name', $data['name']);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Squadra salvata con successo']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Errore nel salvataggio della Squadra']);
}
?>