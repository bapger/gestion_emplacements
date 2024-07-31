<?php
use App\SQLiteConnection;

if (!function_exists('ouverture_bdd')) {
    function ouverture_bdd(){
        try {
            // Connexion à la base de données SQLite
            $pdo = (new SQLiteConnection())->connect();
            return $pdo;
        } catch (PDOException $e) {
            // En cas d'erreur lors de la connexion, afficher un message d'erreur et arrêter l'exécution du script
            echo "Erreur de connexion à la base de données : " . $e->getMessage();
            die();
        }
    }
}

if (!function_exists('fermeture_bdd')) {
    function fermeture_bdd($pdo){
        // Fermeture de la connexion à la base de données
        $pdo = null;
    }
}

if (!function_exists('update_camion_state')) {
    function update_camion_state($pdo, $buttonId, $newState, $value, $ipAddress) {
        try {
            // Récupérer l'ID et l'état actuel du bouton
            echo $newState;
            $stmt = $pdo->prepare("SELECT camion_value, state FROM buttons WHERE id = ?");
            $stmt->execute([$buttonId]);
            $button = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($button) {
                $currentButtonState = $button['state'];

                // Vérifier et mettre à jour l'état du camion
                if (in_array($newState, ["En cours", "Disponible", "Plein", "TDR", "Sigma"])) {
                    if (($currentButtonState === "Disponible" && $newState === "Plein") || ($currentButtonState === "Plein" && in_array($newState, ["TDR", "Sigma"]))) {
                        $stmt = $pdo->prepare("UPDATE camion SET state = ? WHERE value = ?");
                        $stmt->execute([$newState, $value]);
                    }
                    else if (in_array($currentButtonState, ["TDR", "Sigma"]) && $newState === "Disponible"){
                        $stmt = $pdo->prepare("UPDATE camion SET state = ? WHERE value = ?");
                        $stmt->execute(['Fini', $value]);  
                    }
                    // Mettre à jour le bouton avec le camion lié et son état
                    $stmt = $pdo->prepare("UPDATE buttons SET camion_value = ?, state = ? WHERE id = ?");
                    $stmt->execute([$value, $newState, $buttonId]);

                    // Enregistrer le changement dans l'historique
                    $stmt = $pdo->prepare("INSERT INTO camion_changes (button_id, camion_value, new_state, ip_address) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$buttonId, $value, $newState, $ipAddress]);

                    echo "<script>console.log('Camion state updated successfully. IP: $ipAddress, QR Code: $value, Time: ' + new Date().toISOString());</script>";
                    echo "Camion state updated successfully.";
                } else {
                    echo "L'état du camion est invalide.";
                }
            } else {
                echo "Button not found.";
            }
        } catch (PDOException $e) {
            echo "Erreur lors de la mise à jour de l'état du camion : " . $e->getMessage();
        }
    }
}