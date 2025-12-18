<?php
// save.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json_data = file_get_contents('php://input');
    if ($json_data) {
        file_put_contents('q_table.json', $json_data);
        echo "Succès";
    } else {
        echo "Erreur : données vides";
    }
}
?>