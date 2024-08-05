
<?php


use model\util\Instances;
use model\util\Util;

require_once __DIR__ . "/../model/util/Instances.php";
require_once __DIR__ . "/../model/util/Util.php";
require_once __DIR__ . "/../model/forum/ForumEntry.php";
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

    $delete_post_id = $_POST["delete_post_id"];

    if (empty($_POST["csrf_token"])) {
        $_SESSION["message"] = "Ungültige Anfrage. Bitte versuche es erneut!";
        header($redirect_header);
        exit;
    }

    $token = $_POST["csrf_token"];
    $formId = FormName::FORUM_POST_DELETE->name . "-" . $delete_post_id;
    if (!verifyCsrfToken($formId, $token)){
        $_SESSION["message"] = "Ungültige Anfrage. Bitte versuche es erneut!";
        header($redirect_header);
        exit;
    }
    clearToken($token);


    if (!isset($delete_post_id) || $delete_post_id == null) {
        $_SESSION["message"] = "Ungültiger Beitrag";
        header($redirect_header);
        exit;
    }

    if (!is_numeric($delete_post_id)) {
        $_SESSION["message"] = "Ungültiger Beitrag";
        header($redirect_header);
        exit;
    }


    try {
        $forum_entry = $forumManager->getForumEntry($delete_post_id);
    } catch (MissingEntryException $e) {
        $_SESSION["message"] = "Dieser Beitrag wurde bereits gelöscht.";
        header($redirect_header);
        exit;
    } catch (InternalErrorException $e) {
        $_SESSION["message"] = "Ein interner Fehler ist aufgetreten";
        header($redirect_header);
        exit;
    }

    $user_id = $_SESSION["user_id"];

    if ($user_id !== $forum_entry->getCreator()) {
        $_SESSION["message"] = "Du bist nicht Autor des Beitrages";
        header($redirect_header);
        exit;
    }

    try {
        $forumManager->deleteForumEntry($delete_post_id, $user_id);
    } catch (InternalErrorException $e) {
        $_SESSION["message"] = "Ein interner Fehler ist aufgetreten";
        header($redirect_header);
        exit;
    } catch (MissingEntryException $e) {
        $_SESSION["message"] = "Dieser Beitrag existiert nicht";
        header($redirect_header);
        exit;
    }catch (UnauthorizedAccessException) {
        $_SESSION["message"] = "Du bist nicht der Ersteller des Beitrags";
        header($redirect_header);
        exit;
    }

    header($redirect_header);

}
