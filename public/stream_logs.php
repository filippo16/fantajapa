<?php
set_time_limit(0);
require 'db.php';

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('Access-Control-Allow-Origin: *'); // Consente richieste CORS

// Avvia il buffering di output
ob_start();

// Identificatore univoco per questa connessione client
$connectionId = uniqid('client-');
error_log("Nuovo client connesso: {$connectionId}");

$lastLogs = [];

function formatLog($log) {
    return [
        'id' => (int) $log['id'],
        'username' => (string) $log['username'],
        'text' => (string) $log['text'],
        'points' => (int) $log['points'],
        'target' => (string) $log['target'],
        'typeAction' => (string) $log['typeAction'],
        'active' => (bool) $log['active']
    ];
}

while (true) {
    // Controlla se il client si è disconnesso
    if (connection_aborted()) {
        error_log("Client disconnesso: {$connectionId}");
        break; // Esce dal loop per questo client
    }

    try {
        // Recupera tutti i log
        $query = "SELECT * FROM action_logs";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        
        $currentLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Confronta i log attuali con quelli precedenti
        $currentLogIds = array_column($currentLogs, 'id');
        $lastLogIds = array_column($lastLogs, 'id');
        
        // Determina i log aggiunti
        $addedLogs = array_filter($currentLogs, function($log) use ($lastLogIds) {
            return !in_array($log['id'], $lastLogIds);
        });
        
        // Determina i log eliminati
        $deletedLogIds = array_diff($lastLogIds, $currentLogIds);
        
        // Invia i log aggiunti
        foreach ($addedLogs as $log) {
            $data = [
                'type' => 'update',
                'log' => formatLog($log)
            ];
            echo "data: " . json_encode($data) . "\n\n";
        }
        
        // Invia i log eliminati
        foreach ($deletedLogIds as $id) {
            $data = [
                'type' => 'delete',
                'log' => ['id' => (int) $id]
            ];
            echo "data: " . json_encode($data) . "\n\n";
        }
        
        // Forza l'invio dei dati
        ob_flush();
        flush();
        
        // Aggiorna i log precedenti
        $lastLogs = $currentLogs;
    } catch (PDOException $e) {
        error_log("Errore nella query: " . $e->getMessage());
    }

    // Attendi un po' prima di controllare di nuovo (1 secondo)
    sleep(1);
}
?>