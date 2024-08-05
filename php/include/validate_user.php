<?php

use model\util\Instances;
use model\util\Util;

require_once __DIR__ . "/../model/util/Util.php";
require_once __DIR__ . "/../model/util/Instances.php";
require_once __DIR__ . "/../model/user/UserManagerInterface.php";

if (session_status() != PHP_SESSION_ACTIVE) session_start();
if (Util::isLoggedIn()){
    if (isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id'])){
        try {
            Instances::getUserManager()->getUserById($_SESSION["user_id"]);
        } catch (InternalErrorException | UserNotFoundException $e) {
            session_unset();
            session_destroy();

        }
    }


}
