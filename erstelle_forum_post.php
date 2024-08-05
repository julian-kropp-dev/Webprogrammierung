<?php 
require_once __DIR__ . "/php/include/constants.php";
require_once __DIR__ . "/php/model/util/Util.php";
require_once __DIR__ . "/php/CSRF.php"; 

use model\util\Util;
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/erstelle_forum_post.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/footer.css">
    <title>Forum</title>
    <link rel="shortcut icon" type="image/x-icon" href="icons/favicon.png">
    <script src="javascript/jquery-3.7.1.min.js"></script>
    <script> const FLICKR_API_KEY = '<?php echo FLICKR_API_KEY; ?>'; </script>
    <script src="javascript/create_forum_entry.js" defer></script>

</head>

<body>
    <?php
    require_once __DIR__ . "/php/include/validate_user.php";
    include_once "./navbar.php";

    if (!Util::isLoggedIn()) {
        header("Location: ./index.php");
        exit;
    }
    ?>

    <h2>Neuen Beitrag hochladen</h2>

    <div class="create-post">
        <form action='php/controller/erstelle_forum_post_controller.php' autocomplete="on" method="post">
            <?php
            $token = generateCsrfToken();
            addCsrfTokenToSession(FormName::FORUM_UPLOAD_POST->name, $token);
            ?>
            <input type="hidden" name="csrf_token" value="<?= $token ?>">
            <label for="forum_post_title">Titel</label><br>
            <textarea rows="2" name="title" id="forum_post_title" required></textarea><br>

            <div class="forum_post_tag_container">
                <label for="forum_post_tag">Tag</label><br>
                <select name="tag" id="forum_post_tag">
                    <option value="#Diskussion">#Diskussion</option>
                    <option value="#Flughafen">#Flughafen</option>
                    <option value="#PlaneSpottingOrte">#PlaneSpottingOrte</option>
                    <option value="#PlaneSpottingSuche">#PlaneSpottingSuche</option>
                </select><br>
            </div>


            <label for="forum_post_description">Beschreibung</label><br>
            <textarea rows="6" name="description" id="forum_post_description" required></textarea><br>

            <div class="js_hidden" style="display: none">
                <label for="flickr_checkbox">Flickr Foto Integration aktivieren</label>
                <input type="checkbox" id="flickr_checkbox"><br>
            </div>
            <div class="flickr_container" style="display: none">
                <label for="flickr_search">Flickr Bild suchen</label><br>
                <input type="text" id="flickr_search" name="flickr_search">
                <button type="button" id="search_button">Suchen</button>
                <div id="flickr_results"></div>
                <input type="hidden" name="flickr_photo_id" id="flickr_photo_id">
            </div>

            <noscript>
                <div class="no-js-message">
                    <p>Bitte aktivieren Sie JavaScript, um die Flickr-Integration zu nutzen.</p>
                </div>
            </noscript>

            <div class="buttons">
                <input type="submit" value="Beitrag erstellen" id="submit_button">
            </div>
        </form>
    </div>

    <div class="buttons">
        <a href="./forum.php" id="back_button">Zurück</a>
    </div>
    <?php include_once "footer.php" ?>
</body>

</html>