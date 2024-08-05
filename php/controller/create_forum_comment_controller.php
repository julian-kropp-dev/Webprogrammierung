<?php


use model\util\Instances;
use model\util\Util;

require_once __DIR__ . "/../model/util/Instances.php";
require_once __DIR__ . "/../model/util/Util.php";
require_once __DIR__ . "/../model/forum/ForumEntry.php";
require_once __DIR__ . "/../include/validate_user.php";
require_once __DIR__ ."/../CSRF.php";


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $redirect_header = "Location: ./../../forum.php";

    if (!Util::isLoggedIn()) {
        $_SESSION["message"] = "Du bist nicht eingeloggt";
        header($redirect_header);
        exit;
    }
    if (!isset($_POST["post_id"]) || !isset($_SESSION["user_id"]) || !isset($_POST["comment_content"])) {
        $_SESSION["message"] = "missing_parameters";
        header($redirect_header);
        exit;
    }

    if (!is_numeric($_POST["post_id"]) || !is_numeric($_SESSION["user_id"])) {
        $_SESSION["message"] = "Ungültige Forum id";
        header($redirect_header);
        exit;
    }
    $post_id = $_POST["post_id"];
    $user_id = $_SESSION["user_id"];
    $comment_content = $_POST["comment_content"];


    if (empty($_POST["csrf_token"])) {
        $_SESSION["message"] = "Ungültige Anfrage. Bitte versuche es erneut!";
        header($redirect_header);
        exit;
    }

    $token = $_POST["csrf_token"];
    $formId = FormName::FORUM_COMMENT->name . "-" . $post_id;
    if (!verifyCsrfToken($formId, $token)) {
        $_SESSION["message"] = "Ungültige Anfrage. Bitte versuche es erneut!";
        header($redirect_header);
        exit;
    }
    clearToken($token);

    try {
        $forum_entry = Instances::getForumManager()->getForumEntry($post_id);
    } catch (MissingEntryException $e) {
        $_SESSION["message"] = "Der Forumbeitrag wurde nicht gefunden";
        header($redirect_header);
        exit;
    } catch (InternalErrorException $e) {
        $_SESSION["message"] = "Ein interner Fehler ist aufgetreten";
        header($redirect_header);
        exit;
    }
    try {
        Instances::getForumManager()->newForumComment($post_id, $user_id, $comment_content);
    } catch (InternalErrorException $e) {
        $_SESSION["message"] = "Ein interner Fehler ist aufgetreten";
        header($redirect_header);
        exit;
    }

    header("Location: ./../../forum_eintrag.php?id=" . $post_id);


}
