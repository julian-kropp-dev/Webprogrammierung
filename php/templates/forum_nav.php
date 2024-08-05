<div class="navigation_container">
    <nav class="navigation">
        <?php
        // ChatGPT helped with the navigation
        use model\util\Util;

        $getArray = $_GET;
        unset($getArray["page"]);
        $params = htmlspecialchars(http_build_query($getArray));

        // Define the maximum number of page buttons to show on either side of the current page
        $max_pages = 4;

        if ($current_page > 1): ?>
            <a class="nav_button" href="?page=<?= ($current_page - 1) . "&" . $params ?>">Vorherige Seite</a>
        <?php endif; ?>

        <?php
        // Calculate range of pages to show around the current page
        $start = max(1, $current_page - $max_pages);
        $end = min($total_pages, $current_page + $max_pages);

        // Show 'First' page button if not already at the beginning
        if ($start > 1): ?>
            <a class="nav_button" href="?page=1&<?= $params ?>">1</a>
            <span>...</span>
        <?php endif;

        // Iterate through visible pages
        for ($i = $start; $i <= $end; $i++): ?>
            <a class="nav_button <?= ($i == $current_page) ? 'active' : '' ?>"
                href="?page=<?= $i . "&" . $params ?>"><?= $i ?></a>
        <?php endfor;

        // Show 'Last' page button if not already at the end
        if ($end < $total_pages): ?>
            <span>...</span>
            <a class="nav_button" href="?page=<?= $total_pages . "&" . $params ?>"><?= $total_pages ?></a>
        <?php endif;

        // Show 'Next' button if not on the last page
        if ($current_page < $total_pages): ?>
            <a class="nav_button" href="?page=<?= ($current_page + 1) . "&" . $params ?>">Nächste Seite</a>
        <?php endif; ?>
    </nav>

    <!-- Additional navigation or content -->
    <div class="create_forum_entry">
        <?php if (Util::isLoggedIn()): ?>
            <a class="nav_button" href="erstelle_forum_post.php">Neuen Beitrag erstellen</a>
        <?php endif; ?>
    </div>
</div>