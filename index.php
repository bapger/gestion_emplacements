<?php 
require 'vendor/autoload.php';

use App\SQLiteConnection;
use App\SQLiteCreateTable;

$pdo = (new SQLiteConnection())->connect();
$sqlite = new SQLiteCreateTable($pdo);

$sqlite->createTables();
$sqlite->initButtons();
$buttons = $sqlite->fetchButtons();
include 'php/db.php';

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

?>

<!DOCTYPE html> 
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Gestion d'emplacements</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="content">
        <div class="button-grid">
        <?php
            foreach ($buttons as $button) {
                $buttonState = $button['state'] ?? 'Disponible'; // Valeur par défaut si 'state' est null
                echo "<button class='btn btn-primary grid-button' id='btn-{$button['id']}' data-button-id='{$button['id']}' data-button-state='{$buttonState}' onclick='updateButtonState({$button['id']})'>{$button['id']}</button>";
            }
        ?>
        </div>
    </div>
    
        <!-- Modal -->
        <div class="modal fade" id="buttonModal" tabindex="-1" role="dialog" aria-labelledby="buttonModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="buttonModalLabel">Enregistrer une manutention</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h1 id="num">Camion: </h1>
                    <form id="updateForm" method="POST" action="">
                        <input type="hidden" id="buttonNumberInput" name="button_number">
                        <div class="form-group" id="Plein_item" style='display: none;'>
                            <label for="newState">Passage en :</label>
                            <select class="form-control" id="newState" name="new_state">
                                <option value="Sigma">Sigma</option>
                                <option value="TDR">TDR</option>
                                <option value="En cours" style="display: none;">En cours</option>
                                <option value="Disponible" style="display: none;">Disponible</option>
                                <option value="Plein" style="display: none;">Plein</option>
                            </select>
                        </div>
                        <div class="section" id="Disponible_item" style='display: none;'>
                            <div id="my-qr-reader"></div>
                        </div>
                        <p class="font-weight-normal" id="Tri_item" style='display: none;'>Valider le camion et passer l'emplacement en disponible.</p>  
                        <input type="hidden" id="qrValue" name="value">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-secondary" onclick="saveValues()">Continuer</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="js/index.js"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        function updateButtonState(buttonId) {
            const button = $(`#btn-${buttonId}`);
            const buttonState = button.data('button-state');

            $('#buttonNumberInput').val(buttonId);

            var qrCodeSection = document.getElementById('Disponible_item');
            var qrCodeStatus = document.getElementById('Tri_item');
            var selector = document.getElementById('Plein_item');
            console.log("\nqrCodeSection"+qrCodeSection);
            console.log("\nqrCodeStatus"+qrCodeStatus);
            console.log("\nselector"+selector);
            switch (buttonState) {
                case 'Disponible':
                    setSelectorToValue('Plein');
                    qrCodeSection.style.display = 'block';
                    qrCodeStatus.style.display = 'block';
                    selector.style.display = 'none';
                    break;
                case 'Plein':
                    selector.style.display = 'block';
                    setSelectorToValue('Sigma');
                    qrCodeSection.style.display = 'none';
                    qrCodeStatus.style.display = 'none';
                    break;
                case 'TDR':
                case 'Sigma':
                    setSelectorToValue('Disponible');
                    qrCodeSection.style.display = 'none';
                    qrCodeStatus.style.display = 'block';
                    selector.style.display = 'none';
                    break;
                default:
                    qrCodeSection.style.display = 'none';
                    qrCodeStatus.style.display = 'none';
                    selector.style.display = 'none';
            }
        
            $('#buttonModal').modal('show');
        }

        function setSelectorToValue(desiredValue) {
            const selectElement = document.getElementById('newState');
            selectElement.value = desiredValue;
        }
        
        function saveValues() {
            var buttonNumber = document.querySelector('#buttonNumberInput').value;
            var newState = document.getElementById('newState').value;
            var value = sessionStorage.getItem("decodeText");
            const button = $(`#btn-${buttonNumber}`);
            button.data('button-state',newState);
            document.getElementById('qrValue').value = value;

            document.getElementById('updateForm').submit();
        }
    </script>
</body>
</html>
