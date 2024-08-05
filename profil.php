<?php

use model\util\Instances;


require_once __DIR__ . '/php/model/user/UserManagerInterface.php';
require_once __DIR__ . "/php/model/util/Instances.php";
require_once __DIR__ . "/php/model/util/Util.php";
require_once __DIR__ . "/php/include/validate_user.php";
require_once __DIR__ . "/php/CSRF.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: ./anmelden.php');
    exit;
}

$redirectErrorPage = "Location: error.php";
try {
    $userManager = Instances::getUserManager();
} catch (InternalErrorException $e) {
    header($redirectErrorPage);
    exit;
}
$loggedInUserId = $_SESSION['user_id'];
$isOwnProfile = true;
try {
    if (isset($_GET['user_id'])) {
        $selectedUserId = htmlspecialchars($_GET['user_id']);
        $isOwnProfile = ($selectedUserId === $loggedInUserId);
        $selectedUser = $userManager->getUserById($selectedUserId);
    } else {
        $selectedUser = $userManager->getUserById($loggedInUserId);
    }
} catch (InternalErrorException $e) {
    header($redirectErrorPage);
    exit;
} catch (UserNotFoundException $e) {
    $_SESSION["message"] = "Der Benutzer konnte nicht gefunden werden";
    header('Location: ./index.php');
    exit;
}

$isEditing = $_SESSION['isEditing'] ?? false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_changes_button'])) {
    $isEditing = true;
    $_SESSION['isEditing'] = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_edit_mode'])) {
    $isEditing = !$isEditing;
    $_SESSION['isEditing'] = $isEditing;
}

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/profil.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/confirm_popup.css">
    <title>Profil von <?php echo htmlspecialchars($selectedUser->getUsername()); ?></title>
    <link rel="icon" href="icons/favicon.png">
    <script src="javascript/profile.js" defer></script>
</head>
<body>
<div class="container">
    <?php include_once "./navbar.php" ?>
    <div class="profile">
        <!-- Profilfoto und Begrüßung -->
        <div class="profile_container">
            <div class="profile_img_container">
                <img src="<?php echo htmlspecialchars($selectedUser->getProfilePhoto()); ?>" class="profile_img"
                     alt="Profilfoto" width="200" id="profilePicture">
            </div>
            <div class="text_button_container">
                <h1 id="welcome_text">Profil von <?php echo htmlspecialchars($selectedUser->getUsername()); ?></h1>
                <?php if ($isOwnProfile): ?>
                    <button type="button" id="edit_profile_button">Profil bearbeiten</button>
                <?php endif; ?>
            </div>
        </div>

        <input type="hidden" id="isEditing" value="<?php echo $isEditing ? 'true' : 'false'; ?>">

        <div id="profile_info">
            <div id="imageModal" class="modal">
                <span class="close">&times;</span>
                <img class="modal-content" id="modalImage" alt="modal">
            </div>
            <!-- Öffentliche Biografie -->
            <div class="public_about_me_section">
                <h2>Über mich - öffentlich sichtbar</h2>
                <p><strong>Benutzername:</strong> <?php echo htmlspecialchars($selectedUser->getUsername()); ?></p>
                <p><strong>Biografie:</strong></p>
                <p><?php echo nl2br(htmlspecialchars($selectedUser->getBiography())); ?></p>
            </div>
            <!-- Private Biografie (nur für das eigene Profil sichtbar) -->
            <?php if ($isOwnProfile): ?>
                <div class="private_about_me_section">
                    <h2>Über mich - Privat</h2>
                    <a id="mail" href="mailto:<?php echo htmlspecialchars($selectedUser->getEmail()); ?>">E-Mail-Adresse: <?php echo htmlspecialchars($selectedUser->getEmail()); ?></a>
                </div>
            <?php endif; ?>
        </div>
        <!-- Profildaten ändern (nur für das eigene Profil sichtbar) -->
        <?php if ($isOwnProfile): ?>
            <div class="change_profil_settings">
                <!-- Formular für den Dateiupload -->
                <form action="./php/controller/profil_controller.php" method="POST" enctype="multipart/form-data"
                      id="edit_profile_form">
                    <?php
                    $token = generateCsrfToken();
                    addCsrfTokenToSession('profile_edit', $token);
                    ?>
                    <input type="hidden" name="csrf_token" value="<?= $token ?>">
                    <h2>Profil bearbeiten</h2>
                    <label for="profile_pic">Profilbild ändern:</label>
                    <input type="file" id="profile_pic" name="profile_pic" accept="image/*">
                    <button type="submit" class="upload_button">Profilbild hochladen</button>
                    <div id="profil_picture_error" class="error"></div>
                    <label for="username">Benutzername:</label>
                    <input type="text" id="username" name="username"
                           value="<?php echo htmlspecialchars($selectedUser->getUsername()); ?>" required>
                    <label for="email">E-Mail-Adresse:</label>
                    <input type="email" id="email" name="email"
                           value="<?php echo htmlspecialchars($selectedUser->getEmail()); ?>" required>
                    <div id="email_error" class="error"></div>
                    <label for="bio">Biografie:</label>
                    <textarea id="bio" name="bio" rows="4"
                              required><?php echo $selectedUser->getBiography(); ?></textarea>
                    <div id="bio_error" class="error"></div>
                    <label for="password">Neues Passwort:</label>
                    <input type="password" id="password" name="password">
                    <div id="password_error" class="error"></div>
                    <label for="confirmPassword">Passwort bestätigen:</label>
                    <input type="password" id="confirmPassword" name="confirmPassword">
                    <div id="confirm_password_error" class="error"></div>
                    <label for="currentPassword">Aktuelles Passwort:</label>
                    <input type="password" id="currentPassword" name="currentPassword" required>
                    <div id="current_password_error" class="error"></div>
                    <button type="submit" id="save_changes_button">Änderungen speichern</button>
                </form>
            </div>
        <?php endif; ?>
        <!-- Konto löschen (nur für das eigene Profil sichtbar) -->
        <?php if ($isOwnProfile): ?>
            <?php if (isset($_SESSION['error'])) { ?>
                <p class="error"><?php echo htmlspecialchars($_SESSION['error']); ?></p>
                <?php unset($_SESSION['error']); ?>
            <?php } ?>
            <div class="delete_account">
                <h2>Konto löschen</h2>
                <p>️⚠️Diese Aktion kann nicht rückgängig gemacht werden!️⚠️</p>
                <div class="popup" id="delete_account_popup">
                    <div class="popup_content">
                        <div>Willst du deinen Spotter-Account wirklich löschen?</div>
                        <form action="./php/controller/profil_controller.php" method="post" id="delete_account_form">
                            <?php
                            $token = generateCsrfToken();
                            addCsrfTokenToSession('profile_delete', $token);
                            ?>
                            <input type="hidden" name="csrf_token" value="<?= $token ?>">
                            <button class="confirm" name="delete_account_button" type="submit">Konto dauerhaft löschen
                            </button>
                        </form>
                        <button class="close_popup">Abbrechen</button>
                    </div>
                </div>
                <form action="./php/controller/profil_controller.php" method="post" id="delete_account_form">
                    <button name="delete_account_button" type="submit" id="delete_account_button">Konto dauerhaft
                        löschen
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
    <div class="user_list">
        <h2>Liste aller registrierten Benutzer:</h2>
        <ul class="user_list_users">
            <?php try {
                foreach ($userManager->getAllUsers() as $user): ?>
                    <li>
                        <a href="<?php echo ($user->getId() === $loggedInUserId) ? './profil.php' : './profil.php?user_id=' . htmlspecialchars($user->getId()); ?>"
                           class="profile"><?php echo htmlspecialchars($user->getUsername()); ?></a></li>
                <?php endforeach;
            } catch (InternalErrorException $e) {
                //ignored, just don't show user-list then
            } ?>
        </ul>
    </div>
</div>
<?php include_once "footer.php" ?>
</body>
</html>
