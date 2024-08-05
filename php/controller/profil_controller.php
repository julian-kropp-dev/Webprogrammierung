<?php

use model\user\User;
use model\util\Instances;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../model/util/Instances.php";
require_once __DIR__ . "/../model/util/Util.php";
require_once __DIR__ . "/../include/validate_user.php";
require_once __DIR__ . "/../CSRF.php";


if (!isset($_SESSION['logged'])) {
    header('Location: ../../anmelden.php');
    exit;
}

try {
    $userManager = Instances::getUserManager();
} catch (InternalErrorException $e) {
    $_SESSION['error'] = "Ein interner Fehler ist aufgetreten";
    header('Location: ../../profil.php');
    exit;
}
$loggedInUserId = $_SESSION['user_id'];
try {
    $loggedInUser = $userManager->getUserById($loggedInUserId);
} catch (InternalErrorException $e) {
    $_SESSION['error'] = "Ein interner Fehler ist aufgetreten";
    header('Location: ../../profil.php');
    exit;
} catch (UserNotFoundException $e) {
    $_SESSION['error'] = "Dein Account ist gelöscht";
    header('Location: ../../profil.php');
    exit;
}

$currentEmail = $loggedInUser->getEmail();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_account_button'])) {
    $token = $_POST["csrf_token"];
    if (empty($token) || !verifyCsrfToken('profile_delete', $token)) {
        $_SESSION["error"] = "Ungültige Anfrage. Bitte versuche es erneut!";
        header('Location: ../../anmelden.php');
        exit;
    }
    clearToken($token);
    try {
        $userManager->deleteUserById($loggedInUserId);

        session_unset();
        session_destroy();

        header('Location: ../../anmelden.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = "Beim Löschen des Kontos ist ein Fehler aufgetreten.";
        header('Location: ../../profil.php');
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST["csrf_token"];
    if (empty($token) || !verifyCsrfToken('profile_edit', $token)) {
        $_SESSION["error"] = "Ungültige Anfrage. Bitte versuche es erneut!";
        header('Location: ../../anmelden.php');
        exit;
    }
    clearToken($token);
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $bio = $_POST['bio'];
    $confirmPassword = $_POST['confirmPassword'];
    $currentPassword = $_POST['currentPassword'];
    $profilePhoto = $loggedInUser->getProfilePhoto();

    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] != UPLOAD_ERR_NO_FILE) {
        $upload_directory = realpath(__DIR__ . '/../../data/images/profile_pictures/') . '/';
        $allowed_extensions = ['png', 'jpg', 'jpeg'];
        $extension = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $allowed_extensions)) {
            $_SESSION['error'] = "Nur PNG, JPG und JPEG Dateien sind erlaubt.";
            header('Location: ../../profil.php');
            exit;
        }

        $filename = $upload_directory . $loggedInUserId . '.' . $extension;
        $oldProfilePhoto = $loggedInUser->getProfilePhoto();

        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $filename)) {
            if ($oldProfilePhoto && file_exists($oldProfilePhoto)) {
                unlink($oldProfilePhoto);
            }
            $profilePhoto = 'data/images/profile_pictures/' . $loggedInUserId . '.' . $extension;
        } else {
            $_SESSION['error'] = "Beim Hochladen des Profilbildes ist ein Fehler aufgetreten: " . $_FILES['profile_pic']['error'] . ". Ziel-Pfad: " . $filename;
            header('Location: ../../profil.php');
            exit;
        }
    }

    try {
        if (!empty($currentPassword) && !$userManager->isValidCredentials($currentEmail, $currentPassword)) {
            $_SESSION['error'] = "Das aktuelle Passwort ist nicht korrekt.";
            header('Location: ../../profil.php');
            exit;
        }
    } catch (InternalErrorException $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: ../../profil.php');
        exit;
    } catch (IncorrectCredentialsException|UnknownEmailException $e) {
        $_SESSION['error'] = "Fehlerhafte Eingabe.";
        header('Location: ../../profil.php');
        exit;
    }

    if (!empty($password) && $password !== $confirmPassword) {
        $_SESSION['error'] = "Das neue Passwort stimmt nicht mit der Bestätigung überein.";
        header('Location: ../../profil.php');
        exit;
    }

    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    } else {
        $hashedPassword = $loggedInUser->getPassword();
    }

    $updatedUser = new User($loggedInUserId, $email, $hashedPassword, $username, $bio, $profilePhoto);

    try {
        $loggedInUser->setProfilePhoto($profilePhoto);
        $userManager->updateUser($updatedUser);

        header('Location: ../../profil.php');
        exit;
    } catch (InternalErrorException $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: ../../profil.php');
        exit;
    } catch (IncorrectCredentialsException|UserAlreadyExistsException|UserNotFoundException|UnknownEmailException $e) {
        $_SESSION['error'] = "Fehlerhafte Eingabe.";
        header('Location: ../../profil.php');
        exit;
    }
}
