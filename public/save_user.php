<?php
require 'db.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON input');
        }

        $name = $data['name'];

        $stmt = $pdo->prepare("INSERT INTO users (name, score, role, members) VALUES (:name, 0, 'user', '')");
        $stmt->bindParam(':name', $name);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Utente salvato!"]);
        } else {
            throw new Exception('Errore durante il salvataggio dell\'utente.');
        }
    } else {
        throw new Exception('Invalid request method');
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>