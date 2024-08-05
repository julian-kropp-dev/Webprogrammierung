<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/anmelden.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/footer.css">
    <script src="javascript/validation.js" defer></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <?php

    use model\util\Util;

    require_once __DIR__ . "/php/include/constants.php";
    require_once __DIR__ . "/php/CSRF.php";
    ?>
    <title>Anmelden</title>
    <link rel="icon" href="icons/favicon.png">
</head>
<body>
<?php
require_once __DIR__ . "/php/include/validate_user.php";
require_once __DIR__ . "/php/model/util/Util.php";
include_once "./navbar.php";

if (Util::isLoggedIn()) {
    header("Location: ./index.php");
    exit;
}
?>
<br>
<br>

<div class="signin_and_up_container">
    <div class="signin">
        <h1>Anmelden</h1>
        <!-- Anmeldeformular -->
        <form action="./php/controller/anmelden_controller.php" method="POST" id="signin-form">
            <?php
            $token = generateCsrfToken();
            addCsrfTokenToSession('signin', $token);
            ?>
            <input type="hidden" name="csrf_token" value="<?= $token ?>">
            <!-- E-Mail -->
            <div class="form-group">
                <label for="email">E-Mail-Adresse:</label>
                <input type="email" id="email" name="email"
                       value="<?php echo htmlspecialchars($_SESSION['signin_email'] ?? ''); ?>" required>
            </div>
            <!-- Passwort -->
            <div class="form-group">
                <label for="password">Passwort:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <!-- Fehlermeldung für Anmeldung -->
            <?php if (isset($_SESSION['error'])) { ?>
                <p class="error"><?php echo htmlspecialchars($_SESSION['error']); ?></p>
                <?php unset($_SESSION['error']); ?>
            <?php } ?>
            <button type="submit" id="submit_signin_button" name="signin">Check-in!</button>
        </form>
    </div>

    <div class="signup">
        <h1>Registrieren</h1>
        <!-- Registrierungsformular -->
        <form action="./php/controller/anmelden_controller.php" method="POST" id="register-form">
            <?php
            $token = generateCsrfToken();
            addCsrfTokenToSession('signup', $token);
            ?>
            <input type="hidden" name="csrf_token" value="<?= $token ?>">
            <!-- Benutzername -->
            <div class="form-group">
                <label for="username">Benutzername:</label>
                <input type="text" id="username" name="username"
                       value="<?php echo htmlspecialchars($_SESSION['signup_username'] ?? ''); ?>" required>
            </div>
            <!-- E-Mail -->
            <div class="form-group">
                <label for="reg-email">E-Mail-Adresse:</label>
                <input type="email" id="reg-email" name="email"
                       value="<?php echo htmlspecialchars($_SESSION['signup_email'] ?? ''); ?>" required>
            </div>
            <!-- Passwort -->
            <div class="form-group">
                <label for="reg-password">Passwort:</label>
                <input type="password" id="reg-password" name="password" required>
            </div>
            <!-- Passwort bestätigen -->
            <div class="form-group">
                <label for="confirm-password">Passwort bestätigen:</label>
                <input type="password" id="confirm-password" name="confirm-password" required>
            </div>
            <!-- Nutzungsbedingungen und Datenschutzbedingungen Checkboxen -->
            <div class="form-group checkbox-group">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">Ich akzeptiere die <a href="nutzungsbedingungen.php">Nutzungsbedingungen</a></label>
            </div>
            <div class="form-group checkbox-group">
                <input type="checkbox" id="privacy" name="privacy" required>
                <label for="privacy">Ich akzeptiere die <a href="datenschutz.php">Datenschutzbedingungen</a></label>
            </div>
            <!-- Fehlermeldung für Registrierung -->
            <?php if (isset($_SESSION['registrationError'])) { ?>
                <p class="error"><?php echo htmlspecialchars($_SESSION['registrationError']); ?></p>
                <?php unset($_SESSION['registrationError']); ?>
            <?php } ?>
            <!-- Submit Button -->
            <input type="hidden" name="signup">
            <button class="g-recaptcha" data-sitekey="<?= RECAPTCHA_KEY_PUBLIC ?>" data-callback="onSubmit"
                    id="submit_signup_button">Jetzt-Abheben!
            </button>
        </form>
        <script>
            function onSubmit(token) {
                document.getElementById("register-form").submit();
            }
        </script>
    </div>
</div>

<?php include_once "footer.php" ?>
</body>
</html>
