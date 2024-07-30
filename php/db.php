<?php

if (!function_exists('ouverture_bdd')) {
    function ouverture_bdd(){
        try {
            // Connexion à la base de données SQLite
            $bdd = new PDO('sqlite:../db/bdd.db');
            // Définir le mode d'erreur de PDO sur Exception
            $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $bdd;
        } catch (PDOException $e) {
            // En cas d'erreur lors de la connexion, afficher un message d'erreur et arrêter l'exécution du script
            echo "Erreur de connexion à la base de données : " . $e->getMessage();
            die();
        }
    }
}

if (!function_exists('fermeture_bdd')) {
    function fermeture_bdd($bdd){
        // Fermeture de la connexion à la base de données
        $bdd = null;
    }
}

if (!function_exists('update_camion_state')) {
    function update_camion_state($bdd, $buttonId, $newState, $value, $ipAddress) {
        try {
            // Récupérer l'ID et l'état actuel du bouton
            $stmt = $bdd->prepare("SELECT camion_value, state FROM buttons WHERE id = ?");
            $stmt->execute([$buttonId]);
            $button = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($button) {
                $currentCamionValue = $button['camion_value'];
                $currentButtonState = $button['state'];

                // Vérifier et mettre à jour l'état du camion
                if (in_array($newState, ["Fini", "En cours", "Disponible", "Plein", "TDR", "Sigma"])) {
                    if ($currentButtonState === "Disponible" && $newState === "Plein") {
                        $stmt = $bdd->prepare("UPDATE camion SET state = ? WHERE value = ?");
                        $stmt->execute([$newState, $value]);
                    } else if ($currentButtonState === "Plein" && in_array($newState, ["TDR", "Sigma"])) {
                        $stmt = $bdd->prepare("UPDATE camion SET state = ? WHERE value = ?");
                        $stmt->execute([$newState, $value]);
                    } else if (in_array($currentButtonState, ["TDR", "Sigma"]) && $newState === "Disponible") {
                        $stmt = $bdd->prepare("UPDATE camion SET state = ? WHERE value = ?");
                        $stmt->execute([$newState, $value]);
                    }

                    // Mettre à jour le bouton avec le camion lié et son état
                    $stmt = $bdd->prepare("UPDATE buttons SET camion_value = ?, state = ? WHERE id = ?");
                    $stmt->execute([$value, $newState, $buttonId]);

                    // Enregistrer le changement dans l'historique
                    $stmt = $bdd->prepare("INSERT INTO camion_changes (button_id, camion_value, new_state, ip_address) VALUES (?, ?, ?, ?)");
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





