<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/beitrag.css" rel="stylesheet">
    <link href="css/navbar.css" rel="stylesheet">
    <link href="css/confirm_popup.css" rel="stylesheet">
    <link rel="stylesheet" href="css/footer.css">
    <title>Fotos</title>
    <link href="icons/favicon.png" rel="shortcut icon" type="image/x-icon">
    <script src="javascript/beitrag.js"></script>
</head>

<body>
    <?php

    use model\posts\PostNotFoundException;
    use model\util\Instances;
    use model\util\Util;

    include_once __DIR__ . "/navbar.php";
    require_once __DIR__ . "/php/model/user/user.php";
    require_once __DIR__ . "/php/model/posts/Post.php";
    require_once __DIR__ . "/php/model/posts/Comment.php";
    require_once __DIR__ . "/php/model/user/UserManagerInterface.php";
    require_once __DIR__ . "/php/model/posts/PostManagerInterface.php";
    require_once __DIR__ . "/php/model/util/Instances.php";
    require_once __DIR__ . "/php/model/util/Util.php";
    require_once __DIR__ . "/php/include/validate_user.php";
    require_once __DIR__ . "/php/CSRF.php";

    $post = null;

    if (!isset($_GET["post_id"])) {
        $_SESSION["message"] = "Dieser Beitrag existiert nicht";
        header("Location: ./index.php");
        exit;
    }
    $post_id = htmlspecialchars($_GET["post_id"]);

    if (!is_numeric($post_id)) {
        $_SESSION["message"] = "Ungültiger Beitrag";
        header("Location: ./index.php");
        exit;
    }
    $postManger = null;
    $redirectErrorPage = "Location: error.php";
    try {
        $postManger = Instances::getPostManager();
    } catch (InternalErrorException $e) {
        header($redirectErrorPage);
        exit;
    }

    try {
        $post = $postManger->getPostById($post_id);
    } catch (PostNotFoundException) {
        $_SESSION["message"] = "Dieser Beitrag existiert nicht";
        header("Location: ./index.php");
        exit;
    } catch (InternalErrorException $e) {
        header($redirectErrorPage);
        exit;
    }

    ?>

    <div class="post_container">
        <a href="./profil.php?user_id=<?= urlencode($post->getUser()->getId()) ?>">
            <div id="header">
                <img alt="Profilfoto" id="profile_picture" src="<?= htmlentities($post->getUser()->getProfilePhoto()) ?>">
                <div id="right_elements">
                    <div id="user_name"><?= htmlentities($post->getUser()->getUsername()) ?></div>
                    <div id="title"><?= htmlentities($post->getTitle()) ?></div>
                </div>
            </div>
        </a>

        <div class="post">
            <?php try {
                if ($postManger->hasPost($post->getId() + 1)) : ?>
                    <a href=" <?= "./beitrag.php?post_id=" . urlencode($post->getId() + 1) ?>" id="back">
                        <img alt="Vorheriges Bild" id="previous_image" src="icons/chevron-left-solid.svg">
                    </a>
                <?php endif;
            } catch (InternalErrorException $e) {
               //Ignoring because it's not a main functionality
            } ?>

            <img alt="Bild" id="image" src="<?= htmlentities($post->getImageSrc()) ?>">
            <?php try {
                if ($postManger->hasPost($post->getId() - 1)) : ?>

                    <a href="<?= "./beitrag.php?post_id=" . urlencode($post->getId() - 1) ?>" id="next">
                        <img alt="Nächstes Bild" id="next_image" src="icons/chevron-right-solid.svg">
                    </a>
                <?php endif;
            } catch (InternalErrorException $e) {
                //Ignoring because it's not a main functionality
            } ?>


        </div>

        <div id="tags">
            <div id="plane_info_tags">
                <div class="tag">
                    <div class="tag_name">Aufnahmedatum:</div>
                    <div class="tag_value"><?= htmlentities($post->getDate()->format("d.m.Y")) ?></div>
                </div>
                <div class="tag">
                    <div class="tag_name">Luftfahrzeugkennzeichen:</div>
                    <div class="tag_value"><?= htmlentities($post->getRegNumber()) ?></div>
                </div>
                <div class="tag">
                    <div class="tag_name">Hersteller:</div>
                    <div class="tag_value"><?= htmlentities($post->getManufacturer()) ?></div>
                </div>
                <div class="tag">
                    <div class="tag_name">Typ:</div>
                    <div class="tag_value"><?= htmlentities($post->getType()) ?></div>
                </div>
                <div class="tag">
                    <div class="tag_name">Flughafen:</div>
                    <div class="tag_value"><?= htmlentities($post->getAirport()) ?></div>
                </div>
            </div>

            <div id="camera_tags">
                <div class="tag">
                    <div class="tag_name">Kamera:</div>
                    <div class="tag_value"><?= htmlentities($post->getCamera()) ?></div>
                </div>
                <div class="tag">
                    <div class="tag_name">Objektiv:</div>
                    <div class="tag_value"><?= htmlentities($post->getLens()) ?></div>
                </div>
                <div class="tag">
                    <div class="tag_name">ISO:</div>
                    <div class="tag_value"><?= htmlentities($post->getIso()) ?></div>
                </div>
                <div class="tag">
                    <div class="tag_name">Blende:</div>
                    <div class="tag_value"><?= htmlentities($post->getAperture()) ?></div>
                </div>
                <div class="tag">
                    <div class="tag_name">Verschluss:</div>
                    <div class="tag_value"><?= htmlentities($post->getShutter()) ?></div>
                </div>
            </div>

        </div>

        <?php if (Util::isLoggedIn() && Util::isPostAuthor($post_id)) : ?>
        <form id="edit_form" action="php/controller/posts/update_post.php" method="post">
            <?php
            $token = generateCsrfToken();
            addCsrfTokenToSession(FormName::POST_EDIT->name . "-" . $post->getId(), $token);
            ?>
            <input type="hidden" name="csrf_token" value="<?= $token ?>">
            <input type="hidden" name="post_id" value="<?= $post->getId() ?>">
           <div id="edit_title">
            <label class="tag_name" for="input_title">Titel:</label>
               <br>
            <input class="tag_value" id="input_title" name="title" value="<?= htmlentities($post->getTitle()) ?>" required>
           </div>
            <div id="edit_form_container">
                <div id="edit_plane_info_tags">
                    <div class="tag" id="date">
                        <label for="input_date" class="tag_name">Aufnahmedatum:</label>
                        <input class="tag_value" type="date" name="date" id="input_date" value="<?= $post->getDate()->format("Y-m-d") ?>" required>
                    </div>
                    <div class="tag" id="reg_number">
                        <label for="input_reg_number" class="tag_name">Luftfahrzeugkennzeichen:</label>
                        <input class="tag_value" type="text" name="reg_number" id="input_reg_number" value="<?= $post->getRegNumber() ?>" required>
                    </div>
                    <div class="tag" id="manufacturer">
                        <label for="input_manufacturer" class="tag_name">Hersteller:</label>
                        <input class="tag_value" type="text" name="manufacturer" id="input_manufacturer" value="<?= $post->getManufacturer() ?>" required>
                    </div>
                    <div class="tag" id="type">
                        <label for="input_type" class="tag_name">Typ:</label>
                        <input class="tag_value" type="text" name="type" id="input_type" value="<?= $post->getType() ?>" required>
                    </div>
                    <div class="tag" id="airport">
                        <label for="input_airport" class="tag_name">Flughafen:</label>
                        <input class="tag_value" type="text" name="airport" id="input_airport" value="<?= $post->getAirport() ?>" required>
                    </div>
                </div>

                <div id="edit_camera_tags">
                    <div class="tag" id="camera">
                        <label for="input_camera" class="tag_name">Kamera:</label>
                        <input class="tag_value" type="text" name="camera" id="input_camera" value="<?= $post->getCamera() ?>" required>
                    </div>
                    <div class="tag" id="lens">
                        <label for="input_lens" class="tag_name">Objektiv:</label>
                        <input class="tag_value" type="text" name="lens" id="input_lens" value="<?= $post->getLens() ?>">
                    </div>
                    <div class="tag" id="iso">
                        <label for="input_iso" class="tag_name">ISO:</label>
                        <input class="tag_value" type="text" name="iso" id="input_iso" value="<?= $post->getIso() ?>">
                    </div>
                    <div class="tag" id="aperture">
                        <label for="input_aperture" class="tag_name">Blende:</label>
                        <input class="tag_value" type="text" name="aperture" id="input_aperture" value="<?= $post->getAperture() ?>">
                    </div>
                    <div class="tag" id="shutter">
                        <label for="input_shutter" class="tag_name">Verschluss:</label>
                        <input class="tag_value" type="text" name="shutter" id="input_shutter" value="<?= $post->getShutter() ?>">
                    </div>
                </div>


                <input type="submit" value="Änderungen speichern">
            </div>
        </form>

        <div id="edit_post">
            <button id="edit_post_button">
                <img src="icons/edit.svg" alt="Bearbeiten">
            </button>
            <div class="popup" id="delete_post_popup">
                <div class="popup_content">
                    <div>Beitrag löschen?</div>
                    <form action="php/controller/posts/delete_post.php" method="post">
                        <?php
                        $token = generateCsrfToken();
                        addCsrfTokenToSession(FormName::POST_DELETE->name . "-" . $post->getId(), $token);
                        ?>
                        <input type="hidden" name="csrf_token" value="<?= $token ?>">
                        <input type="hidden" name="post_id" value="<?= $post->getId() ?>">
                        <button class="confirm" type="submit">Beitrag löschen</button>
                    </form>
                    <button class="close_popup">Abbrechen</button>
                </div>
            </div>
            <form action="php/controller/posts/delete_post.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= $token ?>">
                <input type="hidden" name="post_id" value="<?= $post->getId() ?>">
            <button type="submit" id="delete_post_button" class ="delete_button">
                    <img src="icons/trash-solid.svg" alt="Löschen">
                </button>
            </form>

        </div>

        <?php endif; ?>
        <div class="line"></div>
        <div id="comment_like_container">
            <?php
            if (Util::isLoggedIn()) : ?>
                <noscript>
                    <span>Zum Liken JavaScript aktivieren!</span>
                </noscript>
            <?php endif; ?>
            <div id="like_container">

                <button class="like_button">
                    <?php

                    try {
                        if (Util::isLoggedIn() && Instances::getLikeManager()->hasLiked(Util::getUserFromSession(), $post)) :
                            ?>

                            <img id="liked" alt="Likes" src="./icons/heart-solid.svg">
                        <?php else : ?>
                            <img id="not_liked" alt="Likes" src="./icons/heart-regular.svg">
                        <?php endif;
                    } catch (InternalErrorException $e) {
                        header($redirectErrorPage);
                        exit;
                    } ?>
                </button>
                <span class="like_count">Gefällt <?= $post->getLikes() ?> Mal</span>
            </div>
            <?php if (Util::isLoggedIn()) : ?>

            <form method="post">
                <?php
                $token = generateCsrfToken();
                addCsrfTokenToSession(FormName::POST_COMMENT->name . "-" . $post->getId(), $token);
                ?>
                <input type="hidden" name="csrf_token" value="<?= $token ?>">
                <label for="comment">Kommentar schreiben:</label>
                <br>
            <textarea id="comment" name="comment" placeholder="Kommentar schreiben..." rows="3"></textarea>
            <br>
            <input type="submit" value="Posten">
            </form>
            <?php endif; ?>
        </div>


            <div id="comments">
                <?php
                if (isset($_POST["comment"])) {
                    if (!Util::isLoggedIn()) {
                        return;
                    }
                    $user = Util::getUserFromSession();
                    if ($user == null) return;

                    $comment = is_string($_POST["comment"]) ? $_POST["comment"] : "";
                    if ($comment == "") return;

                    $redirect_header = $_SERVER["REQUEST_URI"];
                    if (empty($_POST["csrf_token"])) {
                        $_SESSION["message"] = "Ungültige Anfrage. Bitte versuche es erneut!";
                        header($redirect_header);
                        exit;
                    }

                    $token = $_POST["csrf_token"];
                    $formId = FormName::POST_COMMENT->name . "-" . $post_id;
                    if (!verifyCsrfToken($formId, $token)){
                        $_SESSION["message"] = "Ungültige Anfrage. Bitte versuche es erneut!";
                        header($redirect_header);
                        exit;
                    }
                    clearToken($token);
                    try {
                        $post->addComment($user, htmlentities($comment));
                    } catch (InternalErrorException $e) {
                      $_SESSION["message"] = "Kommentar konnte nicht gepostet werden versuch es später noch einmal";
                      header("Location: index.php");
                      exit;
                    }
                }
                ?>


                <?php
                $comments = $post->getComments();
                foreach (array_reverse($comments) as $comment) {
                    $user = $comment->getUser();
                ?>
                    <div class='comment'>
                        <a class='comment_link' href='./profil.php?user_id=<?= urlencode($user->getId()) ?>'>
                            <img alt='Profilfoto' class='comment_img' src='<?= $comment->getUser()->getProfilePhoto() ?>'>
                            <div class='commenter'><?= htmlentities($user->getUsername()) ?>:</div>
                        </a>
                        <div class='comment_content'><?= htmlentities($comment->getContent()) ?></div>
                        <?php if (Util::isLoggedIn() && $comment->getUser()->getId() == Util::getUserFromSession()->getId()) : ?>

                        <form action="php/controller/posts/delete_comment.php" method="post">
                            <?php
                            $token = generateCsrfToken();
                            addCsrfTokenToSession(FormName::POST_COMMENT_DELETE->name . "-" . $post->getId() . "-" . $comment->getId(), $token);
                            ?>
                            <input type="hidden" name="csrf_token" value="<?= $token ?>">
                            <input type="hidden" name="post_id" value="<?= $post->getId() ?>">
                            <input type="hidden" name="comment_id" value="<?= $comment->getId() ?>">
                            <button type="submit" class="delete_button delete_comment_button">
                                <img src="icons/trash-solid.svg" alt="Bearbeiten">
                            </button>
                        </form>
                            <div class="popup delete_comment_popup">
                                <div class="popup_content">
                                    <div>Kommentar löschen?</div>
                                    <form action="php/controller/posts/delete_comment.php" method="post">
                                        <input type="hidden" name="csrf_token" value="<?= $token ?>">
                                        <input type="hidden" name="post_id" value="<?= $post->getId() ?>">
                                        <input type="hidden" name="comment_id" value="<?= $comment->getId() ?>">
                                        <button class="confirm" type="submit">Kommentar löschen</button>
                                    </form>
                                    <button class="close_popup">Abbrechen</button>
                                </div>
                            </div>

                    <?php endif; ?>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>

    <?php include_once "footer.php" ?>
</body>

</html>