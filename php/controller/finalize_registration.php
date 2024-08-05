<?php

use JetBrains\PhpStorm\NoReturn;
use model\util\Instances;

require_once __DIR__ . '/../model/user/UserManagerInterface.php';
require_once __DIR__ . '/../model/util/Instances.php';
require_once __DIR__ . '/../exceptions/InternalErrorException.php';

if (session_status() != PHP_SESSION_ACTIVE) session_start();

try {
    $userManager = Instances::getUserManager();
} catch (InternalErrorException $e) {
    die("Ein interner Fehler ist aufgetreten");
}

if (isset($_GET['token'])) {
    $token = urldecode($_GET['token']);
    try {
        $registrationData = $userManager->getRegistrationDataByToken($token);
    } catch (InternalErrorException $e) {
        die("Ein interner Fehler ist aufgetreten_1");
    }

    try {
        if ($registrationData !== null) {
            try {

                $userManager->createUser(
                    $registrationData['username'],
                    $registrationData['email'],
                    $registrationData['password'],
                    "",
                    "data/images/profile_pictures/dummy.jpeg",
                    $token
                );

                loginWithHashedPassword($registrationData['email'], $registrationData['password'], $userManager);
            } catch (InternalErrorException $e) {
                die("Ein interner Fehler ist aufgetreten_2");
            }catch (UserAlreadyExistsException $e) {
                die($e->getMessage());
            }
        } else {
            die("Ein interner Fehler ist aufgetreten_3");
        }
    } catch (InternalErrorException $e) {
        die("Ein interner Fehler ist aufgetreten_4");
    }
} else {
    die("Ungültige Anfrage");
}

function loginWithHashedPassword($email, $password, $userDAO): void
{
    try {
        $_SESSION['signin_email'] = $email;
        $user = $userDAO->getUserByEmail($email);
        if ($user && $password == $user->getPassword()) {
            $_SESSION['logged'] = true;
            $_SESSION['user_id'] = $user->getId();
            unset($_SESSION['signin_email']);
            header('Location: ../../profil.php');
            exit();
        } else {
            respond(false, 'Fehlerhafte Eingabe.');
        }
    } catch (UnknownEmailException) {
        respond(false, 'Fehlerhafte Eingabe.');
    }
}

#[NoReturn] function respond($success, $message): void
{
    echo json_encode(['success' => $success, 'message' => $message]);
    exit();
}
