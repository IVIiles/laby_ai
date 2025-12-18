<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$mazeFile = __DIR__ . '/../maze.txt';

if (file_exists($mazeFile)) {
    $content = file_get_contents($mazeFile);
    // Nettoyage des retours à la ligne Windows/Unix
    $lines = explode("\n", str_replace("\r", "", trim($content)));
    $mazeData = array_map('str_split', $lines);
    
    echo json_encode([
        "status" => "success",
        "maze" => $mazeData
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Fichier maze.txt introuvable",
        "path" => $mazeFile
    ]);
}
