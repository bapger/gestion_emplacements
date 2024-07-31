<?php
use App\SQLiteConnection;

if (!function_exists('ouverture_bdd')) {
    function ouverture_bdd() {
        try {
            // Connexion à la base de données SQLite
            $pdo = (new SQLiteConnection())->connect();
            return $pdo;
        } catch (PDOException $e) {
            // En cas d'erreur lors de la connexion, afficher un message d'erreur et arrêter l'exécution du script
            echo "<script>alert('Erreur de connexion à la base de données : " . $e->getMessage() . "');</script>";
            die();
        }
    }
}

if (!function_exists('fermeture_bdd')) {
    function fermeture_bdd($pdo) {
        // Fermeture de la connexion à la base de données
        $pdo = null;
    }
}

if (!function_exists('update_camion_state')) {
    function update_camion_state($pdo, $buttonId, $newState, $value, $ipAddress) {
        try {
            // Récupérer l'ID et l'état actuel du bouton
            $stmt = $pdo->prepare("SELECT camion_value, state FROM buttons WHERE id = ?");
            $stmt->execute([$buttonId]);
            $button = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($button) {
                $currentButtonState = $button['state'];

                // Vérifier et mettre à jour l'état du camion
                if (in_array($newState, ["En cours", "Disponible", "Plein", "TDR", "Sigma"])) {
                    if ($currentButtonState === "Disponible" && $newState === "Plein") {
                        // Ajouter le camion à la table camion
                        $stmt = $pdo->prepare("INSERT INTO camion (value, state) VALUES (?, ?)");
                        $stmt->execute([$value, $newState]);
                    } elseif ($currentButtonState === "Plein" && in_array($newState, ["TDR", "Sigma"])) {
                        $stmt = $pdo->prepare("UPDATE camion SET state = ? WHERE value = ?");
                        $stmt->execute([$newState, $value]);
                    } elseif (in_array($currentButtonState, ["TDR", "Sigma"]) && $newState === "Disponible") {
                        $stmt = $pdo->prepare("UPDATE camion SET state = ? WHERE value = ?");
                        $stmt->execute(['Fini', $value]);
                    }

                    // Mettre à jour le bouton avec le camion lié et son état
                    $stmt = $pdo->prepare("UPDATE buttons SET camion_value = ?, state = ? WHERE id = ?");
                    $stmt->execute([$value, $newState, $buttonId]);

                    // Enregistrer le changement dans l'historique
                    $stmt = $pdo->prepare("INSERT INTO camion_changes (button_id, camion_value, new_state, ip_address) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$buttonId, $value, $newState, $ipAddress]);

                    echo "<script>alert('Camion state updated successfully.');</script>";
                } else {
                    echo "<script>alert('L\'état du camion est invalide.');</script>";
                }
            } else {
                echo "<script>alert('Button not found.');</script>";
            }
        } catch (PDOException $e) {
            echo "<script>alert('Erreur lors de la mise à jour de l\'état du camion : " . $e->getMessage() . "');</script>";
        }
    }
}
?>
