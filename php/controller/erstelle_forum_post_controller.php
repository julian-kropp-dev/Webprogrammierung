<?php
use model\util\Instances;
use model\util\Util;

require_once __DIR__ . "/../model/forum/ForumEntry.php";
require_once __DIR__ . "/../model/util/Instances.php";
require_once __DIR__ . "/../model/util/Util.php";
require_once __DIR__ . "/../include/validate_user.php";
require_once __DIR__ ."/../CSRF.php";

$redirect_header = "Location: ./../../forum.php";
try {
    $forumManager = Instances::getForumManager();
} catch (InternalErrorException $e) {
    $_SESSION["message"] = "Ein interner Fehler ist aufgetreten";
    header($redirect_header);
    exit;
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!Util::isLoggedIn()) {
        $_SESSION["message"] = "Du bist nicht eingeloggt";
        header($redirect_header);
        exit;
    }

    if (empty($_POST["csrf_token"])) {
        $_SESSION["message"] = "Ungültige Anfrage. Bitte versuche es erneut!";
        header($redirect_header);
        exit;
    }

    $token = $_POST["csrf_token"];
    $formId = FormName::FORUM_UPLOAD_POST->name;
    if (!verifyCsrfToken($formId, $token)) {
        $_SESSION["message"] = "Ungültige Anfrage. Bitte versuche es erneut!";
        header($redirect_header);
        exit;
    }
    clearToken($token);


    if (!isset($_POST["title"]) || !isset($_POST["tag"]) || !isset($_POST["description"])) {
        $_SESSION["message"] = "Titel, Tags und Beschreibung müssen ausgefüllt werden";
        header("Location: ./../../erstelle_forum_post.php");
        exit;
    }

    $flickr_photo_id = isset($_POST["flickr_photo_id"]) ? $_POST["flickr_photo_id"] : "";

    try {
        $new_entry_id = $forumManager->newForumEntry($_SESSION["user_id"], $_POST["title"], $_POST["tag"], $_POST["description"], $flickr_photo_id);
    } catch (InternalErrorException $e) {
        $_SESSION["message"] = "Ein interner Fehler ist beim erstellen aufgetreten";
        header($redirect_header);
        exit;
    }
    header("Location: ./../../forum_eintrag.php?id=" . $new_entry_id);
    exit;
}
