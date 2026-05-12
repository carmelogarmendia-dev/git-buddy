<?php
require_once 'c:/proyectos/git-buddy/app/src/Models/FileExplorer.php';


use Models\FileExplorer;

$explorer = new FileExplorer();
$projectPath = 'c:/proyectos/git-buddy';
$explorer->setProjectPath($projectPath);

// Forzar carga de status (ya lo hace setProjectPath)
echo "Detectando estados en: $projectPath\n";

$structure = $explorer->scanDirectory($projectPath);


function printStatus($items, $indent = "") {
    foreach ($items as $item) {
        echo $indent . ($item['type'] === 'directory' ? "[D] " : "[F] ") . $item['name'] . " -> " . $item['status'] . "\n";
        if (!empty($item['children'])) {
            printStatus($item['children'], $indent . "  ");
        }
    }
}

printStatus($structure);
