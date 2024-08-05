<?php
use model\util\Instances;

require_once __DIR__ . "/../model/util/Instances.php";
require_once __DIR__ . "/../model/forum/ForumEntry.php";
require_once __DIR__ . "/../exceptions/MissingEntryException.php";
require_once __DIR__ . "/../include/validate_user.php";

$redirect_header = "Location: ./forum.php";
try {
    $forumManager = Instances::getForumManager();
} catch (InternalErrorException $e) {
    $_SESSION["message"] = "Ein interner Fehler ist aufgetreten";
    header($redirect_header);
    exit;
}

// Ueberpruefung der Parameter
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    $_SESSION["message"] = "Der Beitrag existiert nicht mehr.";
    header("Location: forum.php");
    exit;
}

try {
    $id = intval($_GET["id"]);

    $entry = $forumManager->getForumEntry($id);
} catch (MissingEntryException) {
    $_SESSION["message"] = "Der Beitrag existiert nicht mehr.";
    header($redirect_header);
    exit;
} catch (InternalErrorException $e) {
    $_SESSION["message"] = "Ein interner Fehler ist aufgetreten";
    header($redirect_header);
    exit;
}
