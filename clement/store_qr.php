<?php
include "../php/db.php";
header('Content-Type: application/json');

try {
    // Connexion à la base de données SQLite
    $pdo = ouverture_bdd();

    // Récupérer les données JSON POST
    $data = json_decode(file_get_contents('php://input'), true);
    $qrData = $data['qrData'];

    // Récupérer l'adresse IP de l'utilisateur
    $ipAddress = $_SERVER['REMOTE_ADDR'];

    // Récupérer la date/heure actuelle
    $timestamp = date('Y-m-d H:i:s');

    // Insérer les données dans la base de données
    $stmt = $pdo->prepare('INSERT INTO camion (value,new_state,ip_adress) VALUE (:value,:state,:ip');
    $stmt->bindParam(':value', $qrData, PDO::PARAM_STR);
    $stmt->bindParam(':state', "state", PDO::PARAM_STR);
    $stmt->bindParam(':ip', $ipAddress, PDO::PARAM_STR);
    $stmt->execute();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
