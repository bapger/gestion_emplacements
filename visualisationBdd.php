<?php include "php/db.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualisation de la base de données</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
            background-color: #f0f0f0;
        }
        h1 {
            text-align: center;
        }
        .table-container {
            margin-top: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
    <h1>Contenu de la base de données</h1>

    <div class="table-container">
        <h2>Buttons</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Numéro du bouton</th>
                    <th>Valeur du camion</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    $pdo = ouverture_bdd();
                    $stmt = $pdo->query('SELECT id, button_number, camion_value FROM buttons');
                    
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['button_number']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['camion_value']) . '</td>';
                        echo '</tr>';
                    }
                } catch (Exception $e) {
                    echo '<tr><td colspan="3">Erreur : ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="table-container">
        <h2>Camion</h2>
        <table>
            <thead>
                <tr>
                    <th>Valeur</th>
                    <th>Nouveau statut</th>
                    <th>Adresse IP</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    $stmt = $pdo->query('SELECT value, new_state, ip_address FROM camion');
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($row['value']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['new_state']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['ip_address']) . '</td>';
                        echo '</tr>';
                    }
                } catch (Exception $e) {
                    echo '<tr><td colspan="3">Erreur : ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="table-container">
        <h2>Camion Changes</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ID du bouton</th>
                    <th>Numéro du bouton</th>
                    <th>Valeur du camion</th>
                    <th>Nouveau statut</th>
                    <th>Adresse IP</th>
                    <th>Date/Heure</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    $stmt = $pdo->query('
                        SELECT cc.id, cc.button_id, b.button_number, cc.camion_value, cc.new_state, cc.ip_address, cc.changed_at
                        FROM camion_changes cc
                        JOIN buttons b ON cc.button_id = b.id
                        JOIN camion c ON cc.camion_value = c.value
                    ');
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['button_id']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['button_number']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['camion_value']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['new_state']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['ip_address']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['changed_at']) . '</td>';
                        echo '</tr>';
                    }
                } catch (Exception $e) {
                    echo '<tr><td colspan="7">Erreur : ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
