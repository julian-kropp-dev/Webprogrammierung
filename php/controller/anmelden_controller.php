<?php

use JetBrains\PhpStorm\NoReturn;
use model\util\Instances;
use model\util\Util;

require_once __DIR__ . '/../model/user/UserManagerInterface.php';
require_once __DIR__ . '/../model/user/User.php';
require_once __DIR__ . "/../model/util/Instances.php";
require_once __DIR__ . "/../model/util/Util.php";
require_once __DIR__ . "/../exceptions/IncorrectPasswordException.php";
require_once __DIR__ . "/../exceptions/InternalErrorException.php";
require_once __DIR__ . "/../exceptions/UnknownEmailException.php";
require_once __DIR__ . "/../exceptions/UserAlreadyExistsException.php";
require_once __DIR__ . "/../include/constants.php";
require_once __DIR__ . "/../CSRF.php";

if (session_status() != PHP_SESSION_ACTIVE) session_start();
try {
    $userManager = Instances::getUserManager();
} catch (InternalErrorException $e) {
    $_SESSION['error'] = "Ein interner Fehler ist aufgetreten";
    header('Location: ../../anmelden.php');
    exit;
}

#[NoReturn] function respond($success, $message): void
{
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['signin'])) {
        if (Util::isLoggedIn()) {
            header('Location: ../../index.php');
            exit;
        }
        $email = $_POST['email'];
        $password = $_POST['password'];

        $token = $_POST["csrf_token"];
        if (empty($token) || !verifyCsrfToken('signin', $token)) {
            $_SESSION["error"] = "Ungültige Anfrage. Bitte versuche es erneut!";
            header('Location: ../../anmelden.php');
            exit;
        }
        clearToken($token);

        try {
            login($email, $password, $userManager);
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: ../../anmelden.php');
            exit;
        }
    } elseif (isset($_POST['signup'])) {
        if (!verifyCaptcha()) {
            $_SESSION['error'] = "Captcha-Verifikation fehlgeschlagen. Bitte versuche es erneut";
            header('Location: ../../anmelden.php');
            exit;
        }
        if (Util::isLoggedIn()) {
            header('Location: ../../index.php');
            exit;
        }

        if (!isset($_POST['terms']) || !isset($_POST['privacy'])) {
            $_SESSION['registrationError'] = 'Bitte akzeptiere die Nutzungs- und Datenschutzbedingungen.';
            header('Location: ../../anmelden.php');
            exit;
        }

        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm-password'];

        $token = $_POST["csrf_token"];
        if (empty($token) || !verifyCsrfToken('signup', $token)) {
            $_SESSION["error"] = "Ungültige Anfrage. Bitte versuche es erneut!";
            header('Location: ../../anmelden.php');
            exit;
        }
        clearToken($token);

        try {
            register($email, $password, $username, $confirmPassword, $userManager);
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: ../../anmelden.php');
            exit;
        }
    }
}

function verifyCaptcha(): bool
{
    if (empty($_POST['g-recaptcha-response'])) return false;
    $recaptchaResponse = $_POST['g-recaptcha-response'];

    $recaptchaUrl = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptchaData = [
        'secret' => RECAPTCHA_KEY_SECRET,
        'response' => $recaptchaResponse
    ];

    $recaptchaOptions = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-type: application/x-www-form-urlencoded',
            'content' => http_build_query($recaptchaData)
        ]
    ];

    $recaptchaContext = stream_context_create($recaptchaOptions);
    $recaptchaResult = file_get_contents($recaptchaUrl, false, $recaptchaContext);
    $recaptchaResult = json_decode($recaptchaResult, true);

    if (!$recaptchaResult['success']) {
        return false;
    }
    return true;
}

function login($email, $password, $userDAO): void
{
    try {
        $_SESSION['signin_email'] = $email;
        $user = $userDAO->getUserByEmail($email);
        if ($user && password_verify($password, $user->getPassword())) {
            $_SESSION['logged'] = true;
            $_SESSION['user_id'] = $user->getId();
            unset($_SESSION['signin_email']);
            header('Location: ../../profil.php');
        } else {
            $_SESSION['error'] = 'Fehlerhafte Eingabe.';
            header('Location: ../../anmelden.php');
        }
        exit;
    } catch (UnknownEmailException) {
        $_SESSION['error'] = 'Fehlerhafte Eingabe.';
        header('Location: ../../anmelden.php');
        exit;
    }
}

function register($email, $password, $username, $confirmPassword, $userManager): void
{
    try {
        $_SESSION['signup_email'] = $email;
        $_SESSION['signup_username'] = $username;

        if ($password !== $confirmPassword) {
            $_SESSION['registrationError'] = 'Die Passwörter stimmen nicht überein.';
            header('Location: ../../anmelden.php');
            exit;
        }

        if (!validatePassword($password)) {
            $_SESSION['registrationError'] = 'Das Passwort muss mindestens 8 Zeichen lang sein und Großbuchstaben, Kleinbuchstaben, Zahlen und Sonderzeichen enthalten.';
            header('Location: ../../anmelden.php');
            exit;
        }

        if ($userManager->isEmailRegistered($email)) {
            $content = "Bitte ignoriere die E-Mail, wenn du es nicht warst, der sich versucht hat zu registrieren. Du bist aber bereits registriert. Solltest du dein Passwort vergessen haben, klicke bitte hier (Platzhalter)";
        } else {
            $token = bin2hex(random_bytes(16));
            $userManager->storeRegistrationToken($email, $username, $password, $token);

            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $domain = $_SERVER['HTTP_HOST'];
            $path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/finalize_registration.php?token=" . urlencode($token);

            $link = $protocol . $domain . $path;
            $content = "Bitte ignoriere die E-Mail, wenn du es nicht warst, der sich versucht hat zu registrieren. Ansonsten kopiere folgenden Link in deinen Browser, um die Registrierung abzuschliessen: " . $link;
        }

        file_put_contents(__DIR__ . "/../../data/registrierung.txt", $content);
        header('Location: ../../registrierung_info.php');
        exit;
    } catch (UserAlreadyExistsException|InternalErrorException|Exception $e) {
        respond(false, $e->getMessage());
    }
}


function validatePassword($password): bool
{
    $minLength = 8;
    $hasUpperCase = preg_match('/[A-Z]/', $password);
    $hasLowerCase = preg_match('/[a-z]/', $password);
    $hasNumbers = preg_match('/\d/', $password);
    $hasNonalphas = preg_match('/\W/', $password);
    return strlen($password) >= $minLength && $hasUpperCase && $hasLowerCase && $hasNumbers && $hasNonalphas;
}

// Anmeldung verarbeiten
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signin'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    login($email, $password, $userManager);
}

// Registrierung verarbeiten
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm-password'];
    register($email, $password, $username, $confirmPassword, $userManager);
}

// Abmeldung verarbeiten
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: ../../anmelden.php');
    exit;
}
