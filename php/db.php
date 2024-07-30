<?php
if (!function_exists('ouverture_bdd')) {
    function ouverture_bdd(){
        try {
            // Connexion à la base de données SQLite
            $bdd = new PDO('sqlite:bdd.db');
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
    function update_camion_state($bdd, $buttonNumber, $newState, $value, $ipAddress) {
        // Récupérer l'ID et l'état actuel du bouton
        $stmt = $bdd->prepare("SELECT id, camion_value FROM buttons WHERE button_number = ?");
        $stmt->execute([$buttonNumber]);
        $button = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($button) {
            $buttonId = $button['id'];
            $currentCamionValue = $button['camion_value'];

            // Vérifier si le camion est en "Disponible" et le passer en "Plein"
            $stmt = $bdd->prepare("SELECT new_state FROM camion WHERE value = ?");
            $stmt->execute([$currentCamionValue]);
            $camion = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($camion && $camion['new_state'] === 'Disponible' && $newState === 'Plein') {
                // Mettre à jour l'état du camion
                $stmt = $bdd->prepare("UPDATE camion SET new_state = ?, ip_address = ? WHERE value = ?");
                $stmt->execute([$newState, $ipAddress, $value]);

                // Enregistrer le changement dans l'historique
                $stmt = $bdd->prepare("INSERT INTO camion_changes (button_id, camion_value, new_state, ip_address) VALUES (?, ?, ?, ?)");
                $stmt->execute([$buttonId, $value, $newState, $ipAddress]);

                // Afficher les informations dans la console
                echo "<script>console.log('Camion state updated successfully. IP: $ipAddress, QR Code: $value, Time: ' + new Date().toISOString());</script>";

                echo "Camion state updated successfully.";
            } else {
                echo "Camion is not in 'Disponible' state or incorrect new state.";
            }
        } else {
            echo "Button not found.";
        }
    }
}


