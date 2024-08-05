<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/anmelden.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/footer.css">
    <title>Registrierungsinfo</title>
    <link rel="icon" href="icons/favicon.png">
</head>
<body>
<?php

require_once __DIR__ . "/php/include/validate_user.php";
include_once "./navbar.php";
?>
<h1>Registrierung</h1>
<p>Es wurde eine E-Mail an die angegebene Adresse verschickt mit weiteren Infos. Guck dir die Datei <a
            href="data/registrierung.txt" target="_blank">hier</a> an.</p>
<?php include_once "footer.php" ?>
</body>
</html>
