<?php

use model\util\Instances;
use model\util\Util;

require_once __DIR__ . "/../model/util/Instances.php";
require_once __DIR__ . "/../model/util/Util.php";
require_once __DIR__ . "/../model/forum/ForumEntry.php";
require_once __DIR__ . "/../include/validate_user.php";
require_once __DIR__ ."/../CSRF.php";


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $back_header = "Location: " . $_SERVER["HTTP_REFERER"];
    $redirect_header = "Location: ./../../forum.php";

    if (!Util::isLoggedIn()) {
        header($back_header);
        exit;
    }

    $post_id = $_POST["post_id"];
    $comment_id = $_POST["comment_id"];

    if (empty($_POST["csrf_token"])) {
        $_SESSION["message"] = "Ungültige Anfrage. Bitte versuche es erneut!";
        header($redirect_header);
        exit;
    }

    $token = $_POST["csrf_token"];
    $formId = FormName::FORUM_COMMENT_EDIT->name . "-" . $post_id . "-" . $comment_id;
    if (!verifyCsrfToken($formId, $token)) {
        $_SESSION["message"] = "Ungültige Anfrage. Bitte versuche es erneut!";
        header($redirect_header);
        exit;
    }
    clearToken($token);


    if (!is_numeric($post_id)) {
        $_SESSION["message"] = "Ungültiger Forum Eintrag";
        header($redirect_header);
        exit;
    }
    try {
        $forum_manager = Instances::getForumManager();
    } catch (InternalErrorException) {
        $_SESSION["message"] = "Ein interner Fehler ist aufgetreten";
        header($redirect_header);
        exit;
    }

    try {
        $entry = $forum_manager->getForumEntry($post_id);
    } catch (MissingEntryException) {
        $_SESSION["message"] = "Ungültiger Forum Eintrag";
        header($redirect_header);
        exit;
    } catch (InternalErrorException) {
        $_SESSION["message"] = "Ein interner Fehler ist aufgetreten";
        header($redirect_header);
        exit;
    }

    if ($_SESSION["user_id"] !== $entry->getCommentById($comment_id)->getUserId()) {
        $_SESSION["message"] = "Du bist nicht Autor des Kommentars";
        header($redirect_header);
        exit;
    }
    try {
        $forum_manager->updateForumComment($post_id, $comment_id, $_SESSION["user_id"], $_POST["description"]);
    } catch (InternalErrorException $e) {
        $_SESSION["message"] = "Ein interner Fehler ist aufgetreten";
        header($redirect_header);
        exit;
    } catch (UnauthorizedAccessException) {
        $_SESSION["message"] = "Du bist nicht der Ersteller des Beitrags";
        header($redirect_header);
        exit;
    } catch (MissingEntryException) {
        $_SESSION["message"] = "Der Beitrag existiert nicht";
        header($redirect_header);
        exit;
    }
    header("Location: ./../../forum_eintrag.php?id=" . $post_id);

}
