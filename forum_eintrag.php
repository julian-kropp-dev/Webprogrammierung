<?php
require_once "./php/controller/forum_eintrag_controller.php";
require_once __DIR__ . "/php/model/util/Instances.php";
require_once __DIR__ . "/php/model/util/Util.php";
require_once __DIR__ . "/php/CSRF.php";
require_once __DIR__ . "/php/include/constants.php";

use model\util\Util;

?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/forum_eintrag.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/footer.css">
    <title>Beitrag - Neue Spotting Location</title>
    <link rel="shortcut icon" type="image/x-icon" href="icons/favicon.png">
    <script src="javascript/jquery-3.7.1.min.js"></script>
    <script> const FLICKR_API_KEY = '<?php echo FLICKR_API_KEY; ?>'; </script>
    <script src="javascript/forum_entry.js" defer></script>
</head>

<body>
    <?php
    require_once __DIR__ . "/php/include/validate_user.php";
    include_once "./navbar.php";
    ?>

    <div class="modal" id="customModal">
        <div class="modal-content">
            <p>Möchtest du den Beitrag wirklich löschen?</p>
            <button class="confirm_delete_button" id="confirmDelete">Ja</button>
            <button class="cancel_delete_button" id="cancelDelete">Nein</button>
        </div>
    </div>

    <div class="forum_post_container">
        <div class="forum_post">
            <div class="post_creator_container">
                <h2 class="post_title"><?= htmlspecialchars($entry->getTitle()) ?></h2>
                <h4 class="post_tag"><?= htmlspecialchars($entry->getTags()) ?></h4>
                <div class="post_content">
                    <div class="poster_profile_container">
                        <div class="profile_picture">
                            <a href="profil.php?user_id=<?php echo htmlspecialchars(urlencode($entry->getCreator())); ?>">
                                <img class="profile_img" src="<?php try {
                                    echo htmlspecialchars(Util::getProfilePhotoById($entry->getCreator()))  ;
                                } catch (InternalErrorException | UserNotFoundException) {
                                } ?>" alt="Profilbild" width="50">
                            </a>
                        </div>
                        <h4 class="post_creator">
                            <?php try {
                                echo htmlspecialchars(Util::getUsernameById($entry->getCreator()));
                            } catch (InternalErrorException | UserNotFoundException) {
                                echo htmlspecialchars("Benutzername nicht verfügbar");
                            } ?>
                        </h4>
                    </div>
                    <?php $flickrId = htmlspecialchars($entry->getFlickrId()); ?>
                    <?php if (!empty($flickrId)): ?>
                        <div id="flickr_image_container" data-flickr-id="<?= $flickrId ?>"></div>
                    <?php endif; ?>
                    <h4 class="post_description"><?= htmlspecialchars($entry->getDescription())  ?></h4>
                </div>
            </div>

            <?php if (Util::isLoggedIn() && $_SESSION["user_id"] === $entry->getCreator()): ?>
                <div class="edit_form_container">
                    <form action='php/controller/update_forum_post.php' autocomplete="on" method="post">
                        <?php
                        $token = generateCsrfToken();
                        addCsrfTokenToSession(FormName::FORUM_POST_EDIT->name . "-" . $entry->getId(), $token);
                        ?>
                        <input type="hidden" name="csrf_token" value="<?= $token ?>">

                        <label for="forum_post_title">Titel</label><br>
                        <input type="text" name="title" id="forum_post_title" class="title-input" required
                            value="<?= htmlspecialchars($entry->getTitle()) ?>" maxlength="100"><br>
                        <br>

                        <div class="forum_post_tag_container">
                            <label for="forum_post_tag">Tags</label><br>
                            <select name="tag" id="forum_post_tag">
                                <option value="#Diskussion">#Diskussion</option>
                                <option value="#Flughafen">#Flughafen</option>
                                <option value="#PlaneSpottingOrte">#PlaneSpottingOrte</option>
                                <option value="#PlaneSpottingSuche">#PlaneSpottingSuche</option>
                            </select><br>
                        </div>
                        <br>

                        <label for="forum_post_description">Beschreibung</label><br>
                        <textarea rows="8" name="description" id="forum_post_description"
                            required><?= htmlspecialchars($entry->getDescription()) ?></textarea><br>
                        <br>
                        <input type="hidden" name="post_id" value=<?= htmlspecialchars($entry->getId()) ?>>
                        <div class="buttons">
                            <input type="submit" value="Beitrag verändern" id="submit_button">
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <div class="post_footer">
                <h5 class="post_creation_time">Erstellt am: <?= htmlspecialchars($entry->getCreationTime()) ?></h5>
                <?php if (Util::isLoggedIn() && $_SESSION["user_id"] === $entry->getCreator()): ?>
                    <div class="edit_post">
                        <button type="button" class="edit_post_button" data-post-id="<?= htmlspecialchars($entry->getId()) ?>">
                            <img src="icons/edit.svg" alt="Bearbeiten">
                        </button>
                        <form class="delete_post_form" action="php/controller/delete_forum_post.php" method="post">
                            <?php
                            $token = generateCsrfToken();
                            addCsrfTokenToSession(FormName::FORUM_POST_DELETE->name . "-" . $entry->getId(), $token);
                            ?>
                            <input type="hidden" name="csrf_token" value="<?= $token ?>">
                            <input type="hidden" name="delete_post_id" value=<?= htmlspecialchars($entry->getId()) ?>>
                            <button type="submit" class="delete_post_button">
                                <img src="icons/trash-solid.svg" alt="Löschen">
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

            <?php foreach ($entry->getComments() as $comment): ?>
                <div class="comment">
                    <div class="profile_comment_container" data-comment-id="<?=  $comment->getCommentId() ?>">
                        <div class="profile_container">
                            <div class="profile_picture">
                                <a href="profil.php?user_id=<?php echo htmlspecialchars(urlencode($comment->getUserId())); ?>">
                                    <img class="profile_img" src="<?php try {
                                        echo htmlspecialchars(Util::getProfilePhotoById($comment->getUserId()));
                                    } catch (InternalErrorException | UserNotFoundException $e) {
                                    } ?>" alt="Profilbild" width="50">
                                </a>
                            </div>
                            <h4 class="comment_creator">
                                <?php try {
                                    echo htmlentities(Util::getUsernameById($comment->getUserId()));
                                } catch (InternalErrorException | UserNotFoundException $e) {
                                    echo htmlspecialchars("Benutzername nicht verfügbar");
                                } ?>
                            </h4>
                        </div>
                        <h4 class="comment_text"><?php echo htmlentities($comment->getContent()); ?></h4>
                    </div>
                </div>

                <?php if (Util::isLoggedIn() && Util::getUserFromSession()->getId() == $comment->getUserId()): ?>
                    <form action="php/controller/update_forum_comment.php" class="edit_comment_form_container"
                        data-comment-id="<?= $comment->getCommentId() ?>" method="post">
                        <?php
                        $token = generateCsrfToken();
                        addCsrfTokenToSession(FormName::FORUM_COMMENT_EDIT->name . "-" . $entry->getId() . "-" . $comment->getCommentId(), $token);
                        ?>
                        <input type="hidden" name="csrf_token" value="<?= $token ?>">
                        <label for="comment_description_<?= $comment->getCommentId() ?>">Kommentar bearbeiten</label><br>
                        <textarea rows="4" name="description" class="comment_description"
                            id="comment_description_<?= htmlspecialchars($comment->getCommentId()) ?>"
                            required><?= htmlspecialchars($comment->getContent()) ?></textarea><br>
                        <br>
                        <input type="hidden" name="post_id" value=<?= htmlspecialchars($entry->getId()) ?>>
                        <input type="hidden" name="comment_id" value=<?= htmlspecialchars($comment->getCommentId()) ?>>
                        <div class="buttons">
                            <input type="submit" value="Beitrag verändern" class="submit_button">
                        </div>
                    </form>
                <?php endif; ?>
                <div class="comment_footer">
                    <h5 class="comment_time">Erstellt am: <?= htmlspecialchars($comment->getCreationTime()) ?></h5>
                    <?php if (Util::isLoggedIn() && Util::getUserFromSession()->getId() == $comment->getUserId()): ?>
                        <div class="edit_comment">
                            <button type="button" class="edit_comment_button" data-comment-id="<?= htmlspecialchars($comment->getCommentId()) ?>">
                                <img src="icons/edit.svg" alt="Bearbeiten">
                            </button>

                            <form action="php/controller/delete_forum_comment.php" method="post">
                                <?php
                                $token = generateCsrfToken();
                                addCsrfTokenToSession(FormName::FORUM_COMMENT_DELETE->name . "-" . $entry->getId() . "-" . $comment->getCommentId(), $token);
                                ?>
                                <input type="hidden" name="csrf_token" value="<?= $token ?>">
                                <input type="hidden" name="delete_forum_comment_id"
                                    value="<?php echo htmlspecialchars($comment->getCommentId()); ?>">
                                <input type="hidden" name="forum_entry_id"
                                    value="<?php echo htmlspecialchars($entry->getId()); ?>">
                                <button type="submit" class="delete_comment_button">
                                    <img src="icons/trash-solid.svg" alt="Löschen">
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <div class="comment_container">
                <?php if (Util::isLoggedIn()): ?>
                    <form action="php/controller/create_forum_comment_controller.php" class="comment_form" method="post">
                        <?php
                        $token = generateCsrfToken();
                        addCsrfTokenToSession(FormName::FORUM_COMMENT->name . "-" . $entry->getId(), $token);
                        ?>
                        <input type="hidden" name="csrf_token" value="<?= $token ?>">
                        <label for="comment_content">Kommentar schreiben:</label>
                        <br>
                        <textarea id="comment_content" name="comment_content" placeholder="Kommentar schreiben..." rows="3"
                            required></textarea>
                        <input type="hidden" name="post_id" value=<?= htmlspecialchars($entry->getId()) ?>>
                        <br>
                        <input type="submit" value="Posten">
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php include_once "footer.php" ?>
</body>

</html>