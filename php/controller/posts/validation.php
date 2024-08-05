<?php

use model\util\Util;

/**
 * @param string $redirect_header
 * @return string|void
 */
function validateInput(string $redirect_header)
{
    if (!isset($_POST["post_id"]) || $_POST["post_id"] == null) {
        $_SESSION["message"] = "Ungültiger Beitrag";
        header($redirect_header);
        exit;
    }
    $post_id = $_POST["post_id"];

    if (!is_numeric($_POST["post_id"])) {
        $_SESSION["message"] = "Ungültiger Beitrag";
        header($redirect_header);
        exit;
    }

    if (!Util::isPostAuthor($post_id)) {
        $_SESSION["message"] = "Du bist nicht Autor des Beitrages";
        header($redirect_header);
        exit;
    }
    return $post_id;
}
