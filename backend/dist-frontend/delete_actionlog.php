<?php
require 'db.php';

header('Content-Type: application/json');

function checkUserRole($pdo, $username) {
    $query = "SELECT role FROM users WHERE name = :username";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC)['role'];
}

try {
    // Controlla il metodo della richiesta
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Metodo non consentito']);
        exit;
    }

    // Ottieni il corpo della richiesta
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input');
    }

    // Verifica il ruolo dell'utente
    $userRole = checkUserRole($pdo, $data['name']);
    if ($userRole !== 'mod') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Accesso negato']);
        exit;
    }

    // Elimina il log
    $stmt = $pdo->prepare("DELETE FROM action_logs WHERE id = :id");
    $stmt->bindParam(':id', $data['idToDel']);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Log eliminato con successo']);
    } else {
        throw new Exception('Errore durante l\'eliminazione del log');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>