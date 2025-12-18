<?php
header('Content-Type: application/json');

// Lecture du fichier maze.txt
$mazeFile = __DIR__ . '/../maze.txt';
$mazeData = [];

if (file_exists($mazeFile)) {
    $lines = file($mazeFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $mazeData[] = str_split($line);
    }
} else {
    // Labyrinthe par défaut si le fichier est manquant
    $mazeData = [["A", "1", "0"], ["0", "1", "0"], ["0", "0", "G"]];
}

// Pour l'instant, on renvoie juste la structure du labyrinthe
echo json_encode([
    "status" => "success",
    "maze" => $mazeData
]);