<?php
include 'db.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $buttonNumber = $_POST['button_number'];
    $newState = $_POST['new_state'];
    $value = $_POST['value'];
    $ipAddress = $_SERVER['REMOTE_ADDR'];

    $bdd = ouverture_bdd();

    // Mettre à jour l'état du camion et enregistrer les informations du scan
    update_camion_state($bdd, $buttonNumber, $newState, $value, $ipAddress);

    // Fermer la connexion à la base de données
    fermeture_bdd($bdd);
}

