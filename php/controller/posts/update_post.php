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


    $back_header = "Location: " . $_SERVER["HTTP_REFERER"];
    $redirect_header = "Location: ./../../../index.php";

    if (!Util::isLoggedIn()){
        header($back_header);
        exit;
    }

    $post_id = validateInput($redirect_header);

    if (!Util::validateDate($_POST["date"])) {
        $_SESSION["message"] = "Es wurde keine gültiges Datum angegeben";
        header($redirect_header);
        exit;
    }


    if (empty($_POST["csrf_token"])) {
        $_SESSION["message"] = "Ungültige Anfrage. Bitte versuche es erneut!";
        header($redirect_header);
        exit;
    }

    $token = $_POST["csrf_token"];
    $formId = FormName::POST_EDIT->name . "-" . $post_id;
    if (!verifyCsrfToken($formId, $token)) {
        $_SESSION["message"] = "Ungültige Anfrage. Bitte versuche es erneut!";
        header($redirect_header);
        exit;
    }
    clearToken($token);

    try {
        $post = Instances::getPostManager()->getPostById($post_id);
    } catch (PostNotFoundException $e) {
        $_SESSION["message"] = "Der Post existiert nicht!";
        header("Location: ./../../../index.php");
        exit;
    } catch (InternalErrorException $e) {
        $_SESSION["message"] = "Ein interner Fehler ist aufgetreten, bitte versuche es später nochmal!";
        header($redirect_header);
        exit;
    }


    $fieldSetters = [
        "reg_number" => "setRegNumber",
        "manufacturer" => "setManufacturer",
        "type" => "setType",
        "airport" => "setAirport",
        "camera" => "setCamera",
        "lens" => "setLens",
        "iso" => "setIso",
        "aperture" => "setAperture",
        "shutter" => "setShutter",
        "title" => "setTitle"
    ];


    foreach ($fieldSetters as $field => $setter) {
        $data = $_POST[$field];
        if (isset($data) && is_string($data) && strlen(trim($data)) != 0) {
            $post->{$setter}(trim($_POST[$field]));
        }
    }
    $post->setDate(DateTime::createFromFormat("Y-m-d", $_POST["date"]));

    try {
        Instances::getPostManager()->savePost($post);
    } catch (InternalErrorException $e) {
        $_SESSION["message"] = "Ein interner Fehler ist aufgetreten, bitte versuche es später nochmal!";
        header($redirect_header);
        exit;
    }
    header($back_header);


}
