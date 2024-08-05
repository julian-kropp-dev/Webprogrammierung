<?php

if (session_status() != PHP_SESSION_ACTIVE) {
    session_start();
}
cleanupExpiredTokens();

function generateCsrfToken() : string {
    return bin2hex(random_bytes(32));
}


function addCsrfTokenToSession($formId, $token) : void {
    if (empty($_SESSION['csrf_tokens'])) {
        $_SESSION['csrf_tokens'] = [];
    }
    $expiryTime = time() + 3600; // Token läuft nach 1 Stunde ab
    $_SESSION['csrf_tokens'][$token] = ["form" => $formId, "expiry" => $expiryTime];
}


function verifyCsrfToken($formId, $token) : bool {
    if (isset($_SESSION['csrf_tokens'][$token])) {
        $storedTokenData = $_SESSION['csrf_tokens'][$token];
        if ($storedTokenData['expiry'] > time() && $formId === ($storedTokenData['form'])) {
            return true;
        } else {
            unset($_SESSION['csrf_tokens'][$token]);
        }
    }
    return false;
}

function clearToken($token) : void {
    if (!isset($_SESSION['csrf_tokens'])) {
        return;
    }
    unset($_SESSION["csrf_tokens"][$token]);
}

function cleanupExpiredTokens() : void {
    if (empty($_SESSION['csrf_tokens'])) {
        return;
    }
    $currentTime = time();
    foreach ($_SESSION['csrf_tokens'] as $token => $tokenData) {
        if ($tokenData["expiry"] <= $currentTime) {
            unset($_SESSION['csrf_tokens'][$token]);
        }
    }
}

enum FormName
{
    case POST_EDIT;
    case POST_DELETE;
    case POST_COMMENT;
    case POST_COMMENT_DELETE;
    case UPLOAD_POST;

    case FORUM_UPLOAD_POST;
    case FORUM_POST_EDIT;
    case FORUM_POST_DELETE;
    case FORUM_COMMENT_EDIT;
    case FORUM_COMMENT_DELETE;
    case FORUM_COMMENT;
}