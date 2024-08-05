<?php
require_once __DIR__ . "/ForumEntry.php";
require_once __DIR__ . "/ForumComment.php";
require_once __DIR__ . "/ForumManagerInterface.php";
require_once __DIR__ . "/../user/UserManagerInterface.php";
require_once __DIR__ . "/../util/Util.php";

use model\forum\ForumComment;
use model\forum\ForumManagerInterface;

class DummyForumManager implements ForumManagerInterface
{
    private array $forum_entries = array();

    public function __construct()
    {
        $this->forum_entries[0] = new ForumEntry(0, 1, "Neue Spotting Location in EDDF entdeckt - kommt wer mit!?", "#PlaneSpottingSuche", "Hallo Zusammen, ich bin letzten Samstag beim Gassigehen an der Ecke Sandweg links statt rechts abgebogen und konnte es kaum glauben: Nach 200 Metern befindet sich auf der rechten Seite ein Hügel, von dem man perfekte Aussicht auf Runway 25L hat. Hat jemand von euch Bock nächsten Samstag meinen Kampfhund und mich zu begleiten und coole Fotos zu schießen? GaLiGrü Bob ", date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), "");
        $this->forum_entries[1] = new ForumEntry(1, 2, "Title2", "Tags2", "Description2", date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), "");
        $this->forum_entries[2] = new ForumEntry(2, 3, "Title3", "Tags3", "Description3", date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), "");
        $this->forum_entries[3] = new ForumEntry(3, 3, "Was ist der beste Planespotting Ort?", "#Diskussion", "Was sind die schönsten Orte zum Planespotting?", date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), "");
        $this->forum_entries[4] = new ForumEntry(4, 3, "Test Post", "#Diskussion", "Was sind die schönsten Orte zum Planespotting?", date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), "");
        $comments[0] = new ForumComment(0, 0, 2, "Oh ja! Coole Idee. Ich bin dabei. Würde meinen kleinen Mischling mitnehmen,
        wenn dein Kampfhund ihn nicht auffrisst hahahaha
        Passt dir 12:11 Uhr an der Ecke Sandweg? Um 12:31 Uhr landet ne A380 von Ryanair. Könnte cool sein. Gruß", date('Y-m-d H:i:s'));
        $this->forum_entries[0]->setComments($comments);
    }

    /**
     *  Gets all the ids and finds the next possible id. Then creates a new forum entry with the id.
     * 
     * @param int $creator_id
     * @param string $title
     * @param string $tags
     * @param string $description
     * @param string $flickr_photo_id
     * @return int
     * 
     */
    public function newForumEntry(int $creator_id, string $title, string $tags, string $description, string $flickr_photo_id): int
    {
        $existingIds = [];
        foreach ($this->forum_entries as $forum_entry) {
            $existingIds[] = $forum_entry->getId();
        }
        sort($existingIds);
        $nextId = 0;

        foreach ($existingIds as $id) {
            if ($nextId != $id) {
                break;
            }
            $nextId++;
        }
        $loggedInUser = $_SESSION["user_id"];
        $this->forum_entries[$nextId] = new ForumEntry($nextId, $loggedInUser, $title, $tags, $description, date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), $flickr_photo_id);
        return $nextId;
    }

    public function updateForumEntry(int $post_id, int $user_id, string $title, string $tags, string $description) : void
    {
        if (isset($this->forum_entries[$post_id])) {
            $forumEntry = $this->forum_entries[$post_id];
            if ($forumEntry->getCreator() === $user_id) {
                $forumEntry->setTitle($title);
                $forumEntry->setTags($tags);
                $forumEntry->setDescription($description);
            }
        }
    }

    public function newForumComment(int $post_id, int $user_id, string $content) : void
    {
        if (isset($this->forum_entries[$post_id])) {
            $forumEntry = $this->forum_entries[$post_id];
            $comments = $forumEntry->getComments();
            $commentId = count($comments);
            $newComment = new ForumComment($commentId, $post_id, $user_id, $content, date('Y-m-d H:i:s'));
            $comments[$commentId] = $newComment;
            $forumEntry->setComments($comments);
        }
    }



    public function deleteForumComment(int $post_id, int $comment_id, int $user_id): void
    {
        if (isset($this->forum_entries[$post_id])) {
            $forumEntry = $this->forum_entries[$post_id];
            $comments = $forumEntry->getComments();
            if (isset($comments[$comment_id]) && $comments[$comment_id]->getUserId() === $user_id) {
                unset($comments[$comment_id]);
                $forumEntry->setComments($comments);
            }
        }
    }

    public function updateForumComment(int $post_id, int $comment_id, int $user_id, string $description): void
    {
        if (isset($this->forum_entries[$post_id])) {
            $forumEntry = $this->forum_entries[$post_id];
            $comments = $forumEntry->getComments();
            if (isset($comments[$comment_id]) && $comments[$comment_id]->getUserId() === $user_id) {
                $comments[$comment_id]->setDescription($description);
                $comments[$comment_id]->setUpdatedAt(date('Y-m-d H:i:s'));
                $forumEntry->setComments($comments);
            }
        }
    }

    /**
     * @throws MissingEntryException
     */
    public function getForumEntry($id): ForumEntry
    {
        if ($id < 0 || $id >= count($this->forum_entries)) {
            throw new MissingEntryException();
        }
        return $this->forum_entries[$id];
    }
    public function deleteForumEntry($delete_post_id, $user_id): bool
    {
        if (isset($this->forum_entries[$delete_post_id]) && $this->forum_entries[$delete_post_id]->getUserId() === $user_id) {
            unset($this->forum_entries[$delete_post_id]);
            return true; // Deletion successful
        } else {
            return false; // Forum entry with given ID not found
        }
    }

    public function getForumEntries(int $offset, int $limit): array
    {
        return $this->forum_entries;
    }

    public function searchRequest(string $title, string $tags, string $sort_by, int $offset, int $limit): array
    {
        $found_entries = [];
        foreach ($this->forum_entries as $forum_entry) {
            if (stripos($forum_entry->getTitle(), $title) !== false) {
                $found_entries[] = $forum_entry;
            }
        }
        return $found_entries;
    }

    public function getTotalForumEntriesCount(): int
    {
        return count($this->forum_entries);
    }
}
