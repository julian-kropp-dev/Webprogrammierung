<div class="navbar">
    <a href="index.php">
        <img class="logo" src="example_pictures/logo.png" alt="Logo">
    </a>
    <script src="javascript/burger_menu.js" async></script>
    <label for="menu-toggle" class="menu-btn">&#9776;</label>
    <input type="checkbox" id="menu-toggle">
    <div class="menu-items">
        <a href="index.php">Fotos</a>
        <a href="forum.php">Forum</a>
        <a href="profil.php">Profil</a>
        <?php
        require_once __DIR__ . "/php/include/validate_user.php";
        if (session_status() != PHP_SESSION_ACTIVE) session_start();
        if (isset($_SESSION['logged']) && $_SESSION['logged'] === true) {
            echo '<a href="php/controller/anmelden_controller.php?logout=true" class="last_element">Abmelden</a>';
        } else {
            echo '<a href="anmelden.php">Anmelden</a>';
        }
        ?>
    </div>
</div>
