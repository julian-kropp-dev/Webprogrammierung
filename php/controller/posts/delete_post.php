<?php


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
    $post_id = validateInput($redirect_header);


    if (empty($_POST["csrf_token"])) {
        $_SESSION["message"] = "Ungültige Anfrage. Bitte versuche es erneut!";
        header($redirect_header);
        exit;
    }

    $token = $_POST["csrf_token"];
    $formId = FormName::POST_DELETE->name . "-" . $post_id;
    if (!verifyCsrfToken($formId, $token)){
        $_SESSION["message"] = "Ungültige Anfrage. Bitte versuche es erneut!";
        header($redirect_header);
        exit;
    }
    clearToken($token);

    try {
        Instances::getPostManager()->deletePostById($post_id);
    } catch (InternalErrorException $e) {
        $_SESSION["message"] = "Ein interner Fehler ist aufgetreten, bitte versuche es später nochmal!";
        header($redirect_header);
        exit;
    }
    header($redirect_header);


}
