<?php 
require 'vendor/autoload.php';

use App\SQLiteConnection;
use App\SQLiteCreateTable;

$pdo = (new SQLiteConnection())->connect();
$sqlite = new SQLiteCreateTable($pdo);

$sqlite->createTables();
$sqlite->initButtons();
$buttons = $sqlite->fetchButtons();
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
                    <h5 class="modal-title" id="buttonModalLabel">Update Button State</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h1 id="num">Camion: </h1>
                    <form id="updateForm">
                    <input type="hidden" id="buttonNumberInput" name="button_number">
                        <div class="form-group" id="Plein_item" style='visibility: collapse;'>
                            <label for="newState">Passage en :</label>
                            <select class="form-control" id="newState" name="new_state">
                                <option value="sigma">Sigma</option>
                                <option value="tdr">TDR</option>
                            </select>
                        </div>
                        <div class="section" id="Disponible_item">
                            <div id="my-qr-reader"></div>
                        </div>
                        <p class="font-weight-normal" id="Tri_item" style='visibility: collapse;'>Valider le camion et passer l'emplacement en disponible.</p>  
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

            var newStateSelect = document.getElementById('newState');
            var qrCodeSection = document.getElementById('Disponible_item');
            var qrCodeStatus = document.getElementById('Tri_item');

            switch (buttonState) {
                case 'Disponible':
                    newStateSelect.value = 'Plein';
                    qrCodeSection.style.display = 'block';
                    qrCodeStatus.innerHTML = 'Valider le camion et passer l\'emplacement en disponible.';
                    qrCodeStatus.style.display = 'block';
                    break;
                case 'Plein':
                    newStateSelect.value = 'TDR';
                    qrCodeSection.style.display = 'block';
                    qrCodeStatus.innerHTML = 'Sélectionner un état pour le camion.';
                    qrCodeStatus.style.display = 'block';
                    break;
                case 'TDR':
                case 'Sigma':
                    newStateSelect.value = 'Disponible';
                    qrCodeSection.style.display = 'block';
                    qrCodeStatus.innerHTML = 'Mettre l\'emplacement en libre.';
                    qrCodeStatus.style.display = 'block';
                    break;
                default:
                    newStateSelect.value = buttonState;
                    qrCodeSection.style.display = 'none';
                    qrCodeStatus.style.display = 'none';
            }
        
            $('#buttonModal').modal('show');
        }

        function saveValues() {
            var buttonNumberInput= document.querySelector('#buttonNumberInput')
            var buttonNumber = buttonNumberInput.dataset.buttonId;
            var e=document.getElementById('newState');
            var newState = e.options[e.selectedIndex].text;
            var value = sessionStorage.getItem("decodeText");
        
            var xhr = new XMLHttpRequest();

            xhr.onreadystatechange = function () {
                if (this.readyState === XMLHttpRequest.DONE) {
                    if (this.status === 200) {
                        var response = this.responseText;
                        alert(response);
                    
                        $('#buttonModal').modal('hide');
                        location.reload();
                    } else {
                        console.error('Erreur:', this.statusText);
                    }
                }
            };

            xhr.open('POST', 'php/update_button.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            var data = 'button_number=' + encodeURIComponent(buttonNumber) +
                       '&new_state=' + encodeURIComponent(newState) +
                       '&value=' + encodeURIComponent(value);

            xhr.send(data);
        }
</script>
</body>
</html>
