<?php
session_start();
if (isset($_GET['file'])) {
    $file = $_GET['file'];
    if (file_exists($file)) {
        $content = file_get_contents($file);
        echo nl2br(htmlspecialchars($content));
    } else {
        echo "Die Datei wurde nicht gefunden.";
    }
} else {
    echo "Keine Datei angegeben.";
}