<?php
require 'db.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Metodo non consentito']);
        exit;
    }

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input');
    }

    // // Check if typeAction contains 'undo'
    // if (strpos($data['typeAction'], 'undo') !== false) {
    //     $parts = explode('.', $data['typeAction']);
    //     if (count($parts) == 2) {
    //         $idToUndo = intval($parts[1]);

    //         // Update the action_logs table to set active to false
    //         $updateQuery = "UPDATE action_logs SET active = 1 WHERE id = :id";
    //         $updateStmt = $pdo->prepare($updateQuery);
    //         $updateStmt->bindParam(':id', $idToUndo);

    //         if (!$updateStmt->execute()) {
    //             throw new Exception('Errore durante l\'aggiornamento del log');
    //         }
    //     } else {
    //         throw new Exception('Formato typeAction non valido');
    //     }
    // }

    // Prepara e esegui la query di inserimento
    $query = "INSERT INTO action_logs (username, text, points, target, typeAction, active) VALUES (:username, :text, :points, :target, :typeAction, :active)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':username', $data['username']);
    $stmt->bindParam(':text', $data['text']);
    $stmt->bindParam(':points', $data['points']);
    $stmt->bindParam(':target', $data['target']);
    $stmt->bindParam(':typeAction', $data['typeAction']);
    $stmt->bindParam(':active', $data['active']);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Log salvato con successo']);
    } else {
        throw new Exception('Errore nel salvataggio del log');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>