<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/footer.css">
    <script src="javascript/index.js" defer></script>
    <title>Fotos</title>
    <link href="icons/favicon.png" rel="shortcut icon" type="image/x-icon">
</head>

<body>
    <?php

    use model\util\Instances;
    use model\util\Util;


    require_once __DIR__ . "/php/model/user/User.php";
    require_once __DIR__ . "/php/model/util/Instances.php";
    require_once __DIR__ . "/php/model/util/Util.php";
    require_once __DIR__ . "/php/model/posts/PostManagerInterface.php";
    require_once __DIR__ . "/php/include/validate_user.php";

    include_once "./navbar.php" ?>

    <?php

    if (isset($_SESSION["message"])) :
        if (!is_string($_SESSION["message"])) :
            unset($_SESSION["message"]);
        else :
            $error_message = $_SESSION["message"];
            unset($_SESSION["message"]);
            ?>
            <div id="error_container"><?= $error_message ?></div>

        <?php
        endif;
    endif;
    ?>

    <?php if (Util::isLoggedIn()) : ?>
        <div class="button_container">
            <div class="upload">
                <form action="hochladen_beitrag.php">
                    <button type="submit" id="upload_button">Neues Bild hochladen</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <div class="gallery_container">
        <?php
        $posts = array();
        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            $redirect_header = "Location: ./../../../index.php";

            if (empty($_POST["offset_id"]) || !is_numeric($_POST["offset_id"])) {
                header($redirect_header);
                exit;
            }
            $offset_id = $_POST["offset_id"];

            try {
                $posts = Instances::getPostManager()->getPostsDescWithId($offset_id, 12);
            } catch (InternalErrorException $e) {
            }
        }
        if (empty($posts)) {
            try {
                $posts = Instances::getPostManager()->getPostsDesc(12);
            } catch (InternalErrorException $e) {
            }
        }

        foreach ($posts as $post) :
            $username = $post->getUser()->getUsername();
            $profile_photo = $post->getUser()->getProfilePhoto();

            ?>
            <div class="img_container">
                <div hidden="hidden" class="post_id"><?= $post->getId() ?></div>
                <div class="img_header">
                    <h3 class="img_title"><?= $post->getTitle() ?></h3>
                    <h4 class="img_location">Ort: <?= $post->getAirport() ?></h4>
                    <div class="profile_wrapper">
                        <a href="profil.php?user_id=<?= urlencode($post->getUser()->getId()) ?>">
                            <img class="profile_img" src="<?= $profile_photo ?>" alt="Profilbild" width="50">
                        </a>
                        <a class="profile_name"
                           href="profil.php?user_id=<?= urlencode($post->getUser()->getId()) ?>"><?= $username ?></a>
                    </div>
                </div>
                <a href="beitrag.php?post_id=<?= urlencode($post->getId()) ?>">
                    <img class="img" src="<?= $post->getImageSrc() ?>" alt="Beitrag">
                </a>
            </div>
        <?php endforeach; ?>

    </div>
    <noscript>
        <div id="load_div">
            <form method="post">
                <input type="hidden" name="offset_id" value="<?= empty($posts) ? 0 : $posts[count($posts) - 1]->getId() ?>">
                <button type="submit" id="load_more_posts">Lade ältere Beiträge</button>
            </form>
        </div>
    </noscript>

    <?php include_once "footer.php" ?>
</body>

</html>
