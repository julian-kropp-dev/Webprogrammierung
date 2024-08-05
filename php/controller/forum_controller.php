<?php
require_once "./php/model/forum/DummyForumManager.php";
require_once "./php/model/util/Instances.php";
require_once "./php/model/util/Util.php";
require_once "./php/include/validate_user.php";

use model\util\Util;
use model\util\Instances;

// Define constants
define('ENTRIES_PER_PAGE', 20); // Change this as needed

// Get current page from URL parameter
$current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start_index = ($current_page - 1) * ENTRIES_PER_PAGE;

$redirect_header = "Location: error.php";

try {
    $forumManager = Instances::getForumManager();
} catch (InternalErrorException $e) {
    header($redirect_header);
    exit;
}

// Check if the keys are set before accessing them
$search_title = isset($_GET["search_title"]) ? htmlspecialchars($_GET["search_title"]) : "";
$search_tags = isset($_GET["search-by-tags"]) ? htmlspecialchars($_GET["search-by-tags"]) : "";
$sort_by = isset($_GET["sort_by"]) ? htmlspecialchars($_GET["sort_by"]) : "";

try {
    // Initialize forum_entries and total_pages
    $forum_entries = [];
    $total_pages = 1;

    if (!empty($search_title) || !empty($search_tags) || !empty($sort_by)) {
        // Perform search request
        $search_result = $forumManager->searchRequest($search_title, $search_tags, $sort_by, $start_index, ENTRIES_PER_PAGE);
        $forum_entries = $search_result['forum_entries'];
        $total_count = $search_result['total_count'];

        // Calculate total number of pages
        $total_pages = ceil($total_count / ENTRIES_PER_PAGE);
    } else {
        // Get forum entries without search parameters
        $forum_entries = $forumManager->getForumEntries($start_index, ENTRIES_PER_PAGE);

        // Calculate total number of pages
        $total_entries = $forumManager->getTotalForumEntriesCount();
        $total_pages = ceil($total_entries / ENTRIES_PER_PAGE);
    }
    if (!isset($forum_entries)) {
        $forum_entries = [];
    }
} catch (InternalErrorException) {
    $_SESSION["message"] = "Ein interner Fehler ist aufgetreten. Beiträge konnten nicht geladen werden.";
    $forum_entries = [];
    $total_pages = 1;
    header($redirect_header);
    exit;
}