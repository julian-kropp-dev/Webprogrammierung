<?php

use model\posts\PostNotFoundException;
use model\util\Instances;
use model\util\Util;


require_once __DIR__ . "/../../model/util/Instances.php";
require_once __DIR__ . "/../../model/util/Util.php";
require_once __DIR__ . "/validation.php";
require_once __DIR__ . "/../../include/validate_user.php";
require_once __DIR__ ."/../../CSRF.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $redirect_header = "Location: ./../../../index.php";

    if (!Util::isLoggedIn()) {
        $_SESSION["message"] = "Du bist nicht eingeloggt";
        header($redirect_header);
        exit;
    }
    if (!isset($_POST["post_id"]) || $_POST["post_id"] == null || !is_numeric($_POST["post_id"])) {
        $_SESSION["message"] = "Ungültiger Beitrag";
        header($redirect_header);
        exit;
    }
    if (!isset($_POST["comment_id"]) || $_POST["comment_id"] == null || !is_numeric($_POST["comment_id"])) {
        $_SESSION["message"] = "Ungültiger Kommentar";
        header($redirect_header);
        exit;
    }
    $post_id = $_POST["post_id"];
    $comment_id = $_POST["comment_id"];

    if (!Util::isPostCommentAuthor($post_id, $comment_id)){
        $_SESSION["message"] = "Du bist nicht Autor des Beitrages";
        header($redirect_header);
        exit;
    }


    if (empty($_POST["csrf_token"])) {
        $_SESSION["message"] = "Ungültige Anfrage. Bitte versuche es erneut!";
        header($redirect_header);
        exit;
    }

    $token = $_POST["csrf_token"];
    $formId = FormName::POST_COMMENT_DELETE->name . "-" . $post_id . "-" . $comment_id;
    if (!verifyCsrfToken($formId, $token)){
        $_SESSION["message"] = "Ungültige Anfrage. Bitte versuche es erneut!";
        header($redirect_header);
        exit;
    }
    clearToken($token);

    try {
        Instances::getPostManager()->getPostById($post_id)->removeComment($comment_id);
    } catch (PostNotFoundException $e) {
        $_SESSION["message"] = "Beitrag wurde nicht gefunden";
        header($redirect_header);
        exit;
    } catch (InternalErrorException $e) {
        $_SESSION["message"] = "Ein interner Fehler ist aufgetreten, bitte versuche es später nochmal!";
        header($redirect_header);
        exit;
    }
    header("Location: " . $_SERVER["HTTP_REFERER"]);


}
