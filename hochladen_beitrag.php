<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/hochladen_beitrag.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/footer.css">
    <title>Neuen Beitrag hochladen</title>
    <link rel="icon" href="icons/favicon.png">

    <script src="javascript/drag_and_drop.js"></script>
</head>

<body>
<?php

use model\util\Util;

require_once __DIR__ . "/php/model/util/Util.php";
require_once __DIR__ . "/php/include/validate_user.php";
require_once __DIR__ . "/php/CSRF.php";


if (!Util::isLoggedIn()) {
    header("Location: index.php");
    exit;
}

include_once "./navbar.php" ?>

<main>
    <form id="upload_form" action="./php/controller/posts/upload_post.php" method="post" enctype="multipart/form-data">
        <?php
        $token = generateCsrfToken();
        addCsrfTokenToSession(FormName::UPLOAD_POST->name, $token);
        ?>
        <input type="hidden" name="csrf_token" value="<?= $token ?>">
        <section class="upload">
            <h2>Neuen Beitrag hochladen</h2>
            <div id="drag_drop_area" class="upload_form">
                <label for="file_input">Bild hierher ziehen oder klicken, um hochzuladen</label>
                <input type="file" id="file_input" name="image" accept="image/*" required>
            </div>
            <div class="upload_title">
                <label for="post_title">Titel</label><br>
                <textarea id="post_title" name="title" required></textarea><br>
            </div>
            <h3>Tags</h3>
            <div class="tags">
                <p>
                    <label for="record_time">Aufnahmezeitpunkt:</label><br>
                    <input type="date" id="record_time" name="date" required>
                </p>
                <p>
                    <label for="number_plate">Kennzeichen:</label><br>
                    <input type="text" id="number_plate" name="reg_number" required>
                </p>
                <p>
                    <label for="manufacturer">Hersteller:</label><br>
                    <input type="text" id="manufacturer" name="manufacturer" required>
                </p>
                <p>
                    <label for="plane_type">Flugzeug:</label><br>
                    <input type="text" id="plane_type" name="type" required>
                </p>
                <p>
                    <label for="airport">Flughafen:</label><br>
                    <input type="text" id="airport" name="airport" required>
                </p>
                <p>
                    <label for="camera">Kamera:</label><br>
                    <input type="text" id="camera" name="camera" required>
                </p>
                <p>
                    <label for="camera_lens">Objektiv:</label><br>
                    <input type="text" id="camera_lens" name="lens">
                </p>
                <p>
                    <label for="camera_iso">ISO:</label><br>
                    <input type="text" id="camera_iso" name="iso">
                </p>
                <p>
                    <label for="camera_aperture">Blende:</label><br>
                    <input type="text" id="camera_aperture" name="aperture">
                </p>
                <p>
                    <label for="camera_shutter">Shutter:</label><br>
                    <input type="text" id="camera_shutter" name="shutter">
                </p>
            </div>
            <div class="buttons">
                <a href="./index.php" id="back_button">Zurück</a>
                <input type="submit" value="Beitrag erstellen" id="submit_button">
            </div>
        </section>
    </form>
</main>
<?php include_once "footer.php" ?>
</body>

</html>