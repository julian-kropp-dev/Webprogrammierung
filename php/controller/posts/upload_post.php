<?php

use exceptions\PostAlreadyExistsException;
use model\util\Instances;
use model\util\Util;

require_once __DIR__ . "/../../model/util/Instances.php";
require_once __DIR__ . "/../../model/util/Util.php";
require_once __DIR__ . "/../../model/posts/ImageUploadHandler.php";
require_once __DIR__ . "/../../include/validate_user.php";
require_once __DIR__ ."/../../CSRF.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $redirect_header = "Location: ./../../../index.php";

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
    $formId = FormName::UPLOAD_POST->name;
    if (!verifyCsrfToken($formId, $token)){
        $_SESSION["message"] = "Ungültige Anfrage. Bitte versuche es erneut!";
        header($redirect_header);
        exit;
    }
    clearToken($token);

    $requiredFields = array("title", "date", "reg_number", "manufacturer", "type", "airport", "camera");
    foreach ($requiredFields as $field) {
        if (strlen(trim($_POST[$field])) == 0 && false) {
            $_SESSION["message"] = "Du hast nicht alle erforderlichen Felder ausgefüllt!";
            header($redirect_header);
            exit;
        }
    }
    if (!Util::validateDate($_POST["date"])) {
        $_SESSION["message"] = "Es wurde keine gültiges Datum angegeben";
        header($redirect_header);
        exit;
    }

    if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK) {
        incorrectImageRedirect($redirect_header);
    }

    $file_type = $_FILES["image"]["type"];

    if (!str_starts_with($file_type,"image/")) {
        incorrectImageRedirect($redirect_header);
    }



    $uploadDirectory = __DIR__ . '/../../../data/images/posts/';


    $originalFilename = $_FILES["image"]["name"];
    $filenameParts = pathinfo($originalFilename);
    $extension = str_replace("image/", "", $file_type);



    $imgType = exif_imagetype($_FILES["image"]["tmp_name"]);
    if ($imgType != JPG && $imgType != PNG){
        incorrectImageRedirect($redirect_header);
    }


    $postData = array(
        "user_id" => Util::getUserFromSession()->getId(),
        "title" => $_POST["title"],
        "image_src" => "data/images/posts/dummy.jpeg",
        "date" => DateTime::createFromFormat("Y-m-d", $_POST["date"]),
        "reg_number" => $_POST["reg_number"],
        "manufacturer" => $_POST["manufacturer"],
        "type" => $_POST["type"],
        "airport" => $_POST["airport"],
        "camera" => $_POST["camera"],
        "lens" => $_POST["lens"] ?? "",
        "iso" => $_POST["iso"] ?? "",
        "aperture" => $_POST["aperture"] ?? "",
        "shutter" => $_POST["shutter"] ?? ""
    );

    $post = null;
    $imgData =  array();
    $imgData["temp_file_path"] =  $_FILES["image"]["tmp_name"];
    $imgData["extension"] =  $extension;
    $imgData["upload_directory"] =  $uploadDirectory;
    $imgData["img_type"] =  $imgType;
    try {
        $post = Instances::getPostManager()->createPost($postData, $imgData);
    } catch (UserNotFoundException $e) {
        $_SESSION["message"] = "Fehler beim validieren des Nutzers";
        header($redirect_header);
        exit;
    } catch (PostAlreadyExistsException $e) {
        $_SESSION["message"] = "Der Post existiert schon. Versuche es erneuert";
        header($redirect_header);
        exit;
    } catch (InternalErrorException $e) {
        $_SESSION["message"] = "Ein interner Fehler ist aufgetreten, bitte versuche es später nochmal!";
        header($redirect_header);
        exit;
    }



    $_SESSION["message"] = "Beitrag erfolgreich hochgeladen";
    header($redirect_header);
    exit;

}



  function incorrectImageRedirect($redirect_header): void
 {
    $_SESSION["message"] = "Du kannst nur JPG und PNG Bilder hochladen!";
    header($redirect_header);
    exit;
}

