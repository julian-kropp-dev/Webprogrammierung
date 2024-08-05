<?php
require_once "./php/controller/forum_controller.php";
require_once "./php/model/util/Util.php";

use model\util\Util;
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/forum.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/footer.css">
    <title>Forum</title>
    <link rel="shortcut icon" type="image/x-icon" href="icons/favicon.png">
    <script src="javascript/forum.js" defer></script>
</head>

<body>
    <?php include_once "./navbar.php" ?>

    <?php
    if (isset($_SESSION["message"])):
        if (!is_string($_SESSION["message"])):
            unset($_SESSION["message"]);
        else:
            $error_message = htmlspecialchars($_SESSION["message"]);
            unset($_SESSION["message"]);
            ?>
            <div id="error_container"><?= $error_message ?></div>

            <?php
        endif;
    endif;
    ?>
    <br>
    <br>
    <div class="search_container">
        <div class="sorting">
            <form id="sortingForm" method="get" enctype="application/x-www-form-urlencoded">
                <label for="sort_by">Sortieren nach:</label>
                <select id="sort_by" name="sort_by">
                    <option value="newest" <?php if (isset($_GET['sort_by']) && $_GET['sort_by'] == 'newest')
                        echo 'selected'; ?>>Neueste</option>
                    <option value="oldest" <?php if (isset($_GET['sort_by']) && $_GET['sort_by'] == 'oldest')
                        echo 'selected'; ?>>Älteste</option>
                    <option value="most_replies" <?php if (isset($_GET['sort_by']) && $_GET['sort_by'] == 'most_replies')
                        echo 'selected'; ?>>Meiste Antworten</option>
                </select>
                <?php if (!empty($search_title)): ?>
                    <input type="hidden" name="search_title" value="<?= htmlspecialchars($search_title) ?>">
                <?php endif; ?>
                <?php if (!empty($search_tags)): ?>
                    <input type="hidden" name="search-by-tags" value="<?= htmlspecialchars($search_tags) ?>">
                <?php endif; ?>
                <noscript><button type="submit">Suche</button></noscript>
            </form>
        </div>

        <div class="search_by_tags">
            <form id="searchTagsForm" method="get" enctype="application/x-www-form-urlencoded">
                <label for="search-by-tags">Suche nach Tags:</label>
                <select id="search-by-tags" name="search-by-tags">
                    <option value="" <?php if (isset($_GET['search-by-tags']) && $_GET['search-by-tags'] == '')
                        echo 'selected'; ?>>Alle Tags</option>
                    <option value="#Diskussion" <?php if (isset($_GET['search-by-tags']) && $_GET['search-by-tags'] == '#Diskussion')
                        echo 'selected'; ?>>Diskussion</option>
                    <option value="#Flughafen" <?php if (isset($_GET['search-by-tags']) && $_GET['search-by-tags'] == '#Flughafen')
                        echo 'selected'; ?>>Flughafen</option>
                    <option value="#PlaneSpottingOrte" <?php if (isset($_GET['search-by-tags']) && $_GET['search-by-tags'] == '#PlaneSpottingOrte')
                        echo 'selected'; ?>>PlaneSpottingOrte</option>
                    <option value="#PlaneSpottingSuche" <?php if (isset($_GET['search-by-tags']) && $_GET['search-by-tags'] == '#PlaneSpottingSuche')
                        echo 'selected'; ?>>PlaneSpottingSuche
                    </option>
                    <?php if (!empty($search_title)): ?>
                        <input type="hidden" name="search_title" value="<?= htmlspecialchars($search_title) ?>">
                    <?php endif; ?>
                    <?php if (!empty($sort_by)): ?>
                        <input type="hidden" name="sort_by" value="<?= htmlspecialchars($sort_by) ?>">
                    <?php endif; ?>
                </select>
                <noscript><button type="submit">Suche</button></noscript>
            </form>
        </div>

        <div class="search_for_title">
            <form method="GET" id=search-title enctype="application/x-www-form-urlencoded">
                <label for="search_for_title">Nach Beitrag suchen:</label>
                <?php
                $searchTitle = "Suchtitel eingeben...";
                if (!empty($_GET["search_title"])):
                    $searchTitle = htmlspecialchars(($_GET["search_title"]));
                    ?>
                    <input type="text" id="search_for_title" name="search_title"
                        value="<?= htmlspecialchars($searchTitle) ?>">
                <?php else: ?>
                    <input type="text" id="search_for_title" name="search_title" placeholder="Suchtitel eingeben...">
                <?php endif; ?>
                <?php if (!empty($search_tags)): ?>
                    <input type="hidden" name="search-by-tags" value="<?= htmlspecialchars($search_tags) ?>">
                <?php endif; ?>
                <?php if (!empty($sort_by)): ?>
                    <input type="hidden" name="sort_by" value="<?= htmlspecialchars($sort_by) ?>">
                <?php endif; ?>
                <button type="submit">Suche</button>
            </form>
        </div>
    </div>

    <div class="forum-container">
        <div class="forum">
            <?php include "php/templates/forum_nav.php" ?>


            <?php if (empty($forum_entries)): ?>
                <div id="error_container">Es wurden keine Forumeinträge gefunden. Erstelle jetzt einen Post um abzuheben!
                </div>
            <?php else: ?>
                <!-- Iterate through forum entries and generate HTML for each entry -->
                <?php foreach ($forum_entries as $entry): ?>
                    <div class="post">
                        <!-- Profile picture -->
                        <div class="profile_picture">
                            <a href="profil.php?user_id=<?php echo htmlspecialchars(urlencode($entry->getCreator())); ?>">
                                <img class="profile_img" src="<?php try {
                                    echo htmlspecialchars(Util::getProfilePhotoById($entry->getCreator()));
                                } catch (InternalErrorException | UserNotFoundException $e) {
                                } ?>" alt="Profilbild" width="50">
                            </a>
                        </div>

                        <div class="creator-container">
                            <h4 class="post-creator">
                                <a href="profil.php?user_id=<?php echo urlencode($entry->getCreator()); ?>">
                                    <?php try {
                                        echo htmlspecialchars(Util::getUsernameById($entry->getCreator()));
                                    } catch (InternalErrorException | UserNotFoundException $e) {
                                        echo htmlspecialchars("Benutzername nicht verfügbar");
                                    } ?> </a>
                            </h4>
                            <!-- Post creation time -->
                            <h5 class="post_creation_time">Erstellt am:
                                <?php echo htmlspecialchars($entry->getCreationTime()); ?>
                            </h5>
                        </div>


                        <!-- Post tags -->
                        <h4 class="post_tags"><?php echo htmlspecialchars($entry->getTags()); ?></h4>

                        <!-- Post title -->
                        <h3 class="post_title">
                            <a href="./forum_eintrag.php?id=<?php echo urlencode($entry->getId()); ?>">
                                <?php echo htmlspecialchars($entry->getTitle()); ?></a>
                        </h3>

                        <div class="comments-time-container">
                            <h4 class="post_last_comment_time">Letzter Beitrag am:
                                <?php echo htmlspecialchars($entry->getUpdateTime()); ?>
                            </h4>

                            <!-- Post comments count -->
                            <h5 class="post_comments">Antworten: <?php echo htmlspecialchars($entry->getCommentCount()); ?></h5>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php endif; ?>


            <?php include "php/templates/forum_nav.php" ?>
        </div>
    </div>
    <?php include_once "footer.php" ?>
</body>

</html>