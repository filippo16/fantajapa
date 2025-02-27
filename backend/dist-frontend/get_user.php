<?php
include 'db.php'; // Include il file di connessione al database

try {
    // Recupera il parametro 'name' dalla richiesta POST
    $nameInput = $_POST['name'] ?? null;

    // Verifica che il parametro 'name' sia stato fornito
    if ($nameInput === null) {
        echo json_encode([
            "success" => false,
            "message" => "Parametro 'name' non fornito."
        ]);
        exit;
    }

    // Prepara la query per selezionare l'utente specifico
    $stmt = $pdo->prepare("SELECT name, score, role FROM users WHERE name = :name");

    // Esegui la query con il parametro fornito
    $stmt->execute([':name' => $nameInput]);

    // Recupera il risultato
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Restituisci i dati dell'utente in formato JSON
        echo json_encode([
            "success" => true,
            "data" => $user
        ]);
    } else {
        // L'utente non è stato trovato
        echo json_encode([
            "success" => false,
            "message" => "Utente non trovato."
        ]);
    }
} catch (PDOException $e) {
    // Gestisce gli errori ed emette un messaggio JSON
    echo json_encode([
        "success" => false,
        "message" => "Errore nel recupero dell'utente: " . $e->getMessage()
    ]);
}
?>