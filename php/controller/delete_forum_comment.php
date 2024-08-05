<?php


use model\util\Instances;
use model\util\Util;

require_once __DIR__ . "/../model/util/Instances.php";
require_once __DIR__ . "/../model/util/Util.php";
require_once __DIR__ . "/../model/forum/ForumEntry.php";
require_once __DIR__ . "/../include/validate_user.php";
require_once __DIR__ . "/../CSRF.php";


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $redirect_header = "Location: ./../../forum.php";

    if (!Util::isLoggedIn()) {
        $_SESSION["message"] = "Du bist nicht eingeloggt";
        header($redirect_header);
        exit;
    }
    if (!isset($_POST["delete_forum_comment_id"]) || $_POST["delete_forum_comment_id"] == null) {
        $_SESSION["message"] = "Ungültiger Beitrag";
        header($redirect_header);
        exit;
    }

    $post_id = $_POST['forum_entry_id'];
    $comment_id = $_POST["delete_forum_comment_id"];

    if (empty($_POST["csrf_token"])) {
        $_SESSION["message"] = "Ungültige Anfrage. Bitte versuche es erneut!";
        header($redirect_header);
        exit;
    }

    $token = $_POST["csrf_token"];
    $formId = FormName::FORUM_COMMENT_DELETE->name . "-" . $post_id . "-" . $comment_id;
    if (!verifyCsrfToken($formId, $token)) {
        $_SESSION["message"] = "Ungültige Anfrage. Bitte versuche es erneut!";
        header($redirect_header);
        exit;
    }
    clearToken($token);



    if (!is_numeric($_POST["delete_forum_comment_id"])) {
        $_SESSION["message"] = "Ungültiger Beitrag";
        header($redirect_header);
        exit;
    }

    try {
        $forum_entry = Instances::getForumManager()->getForumEntry($_POST['forum_entry_id']);
    } catch (MissingEntryException $e) {
        $_SESSION["message"] = "Ungültiger Beitrag";
        header($redirect_header);
        exit;
    } catch (InternalErrorException $e) {
        $_SESSION["message"] = "Ein interner Fehler ist aufgetreten";
        header($redirect_header);
        exit;
    }
    $comment = $forum_entry->getComments();
    $comment = $forum_entry->getCommentById($_POST['delete_forum_comment_id']);
    if ($comment == null) {
        $_SESSION["message"] = "Der Kommentar existiert nicht";
        header($redirect_header);
        exit;
    }

    $user_id = $_SESSION["user_id"];

    if ($user_id !== $comment->getUserId()) {
        $_SESSION["message"] = "Du bist nicht Autor des Kommentars";
        header($redirect_header);
        exit;
    }

    try {
        Instances::getForumManager()->deleteForumComment($post_id, $comment_id, $user_id);
    } catch (InternalErrorException $e) {
        $_SESSION["message"] = "Ein interner Fehler ist aufgetreten";
        header($redirect_header);
        exit;
    } catch (MissingEntryException $e) {
        $_SESSION["message"] = "Dieser Beitrag existiert nicht";
        header($redirect_header);
        exit;
    } catch (UnauthorizedAccessException) {
        $_SESSION["message"] = "Du bist nicht der Ersteller des Kommentars";
        header($redirect_header);
        exit;
    }

    header("Location: ./../../forum_eintrag.php?id=" . $_POST['forum_entry_id']);


}
