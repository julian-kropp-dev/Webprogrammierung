<?php

use model\forum\ForumComment;
use model\forum\ForumManagerInterface;
use model\util\Util;

require_once __DIR__ . "/ForumManagerInterface.php";
require_once __DIR__ . "/../../exceptions/InternalErrorException.php";
require_once __DIR__ . "/../../exceptions/MissingEntryException.php";
require_once __DIR__ . "/../../exceptions/UnauthorizedAccessException.php";
require_once __DIR__ . "/../util/Util.php";

class ForumManagerSQLite implements ForumManagerInterface
{

    private PDO $connection;


    public function __construct()
    {
        try {
            $this->connection = Util::getDBConnection();
            $sql = $this->connection->query("SELECT name FROM sqlite_master WHERE type='table' AND name='forum';");

            if ($sql->fetch() === false) {
                // 'forum' table does not exist, so create tables
                $this->create();
            }
        } catch (PDOException) {
            throw new InternalErrorException();
        }
    }


    public function newForumEntry($creator_id, $title, $tags, $description, $flickr_photo_id): int
    {
        try {
            $db = $this->connection;
            $sql = "INSERT INTO forum (creator_id, title, tags, description, flickr_photo_id) VALUES (:creator_id, :title, :tags, :description, :flickr_photo_id);";
            $command = $db->prepare($sql);
            if (!$command) {
                throw new InternalErrorException();
            }
            if (
                !$command->execute([
                    ":creator_id" => $creator_id,
                    ":title" => $title,
                    ":tags" => $tags,
                    ":description" => $description,
                    ":flickr_photo_id" => $flickr_photo_id
                ])
            ) {
                throw new InternalErrorException();
            }
            return intval($db->lastInsertId());
        } catch (PDOException) {
            throw new InternalErrorException();
        }
    }


    public function getForumEntry($id): ForumEntry
    {
        try {
            $db = $this->connection;
            $sql = "SELECT f.*, fc.comment_id, fc.post_id, fc.user_id, fc.content, fc.comment_creation_time AS comment_creation_time, f.creation_time AS forum_creation_time
            FROM forum f
            LEFT JOIN forum_comments fc ON f.id = fc.post_id
            WHERE f.id = :id";


            $command = $db->prepare($sql);
            if (!$command) {
                throw new InternalErrorException();
            }
            if (!$command->execute([":id" => $id])) {
                throw new InternalErrorException();
            }
            $result = $command->fetchAll();
            if (empty($result)) {
                throw new MissingEntryException();
            }
            $entry = $result[0];
            $flickr_photo_id = $entry["flickr_photo_id"] ?? '';
            $resultEntry = new ForumEntry($entry["id"], $entry["creator_id"], $entry["title"], $entry["tags"], $entry["description"], $entry["forum_creation_time"], $entry["update_time"], $flickr_photo_id);

            $comments = [];
            foreach ($result as $row) {
                if ($row["comment_id"] !== null) {
                    $comments[] = new ForumComment(
                        $row["comment_id"],
                        $row["post_id"],
                        $row["user_id"],
                        $row["content"],
                        $row["comment_creation_time"]
                    );
                }
            }
            $resultEntry->setComments($comments);

            return $resultEntry;
        } catch (PDOException) {
            throw new InternalErrorException();
        }
    }


    public function updateForumEntry(int $post_id, int $user_id, string $title, string $tags, string $description): void
    {
        try {
            $db = $this->connection;
            $db->beginTransaction();

            // Check if the user owns the forum entry
            $sql = "SELECT creator_id FROM forum WHERE id = :id LIMIT 1";
            $command = $db->prepare($sql);
            if (!$command) {
                $db->rollBack();
                throw new InternalErrorException();
            }
            if (!$command->execute([":id" => $post_id])) {
                $db->rollBack();
                throw new InternalErrorException();
            }
            $result = $command->fetch();
            if (!$result) {
                $db->rollBack();
                throw new MissingEntryException();
            }

            // Check if the user_id matches the creator_id
            if ($result["creator_id"] !== $user_id) {
                $db->rollBack();
                throw new UnauthorizedAccessException();
            }

            $sql = "UPDATE forum SET title = :title, tags = :tags, description = :description, update_time = CURRENT_TIMESTAMP WHERE id = :id";
            $command = $db->prepare($sql);
            if (!$command) {
                $db->rollBack();
                throw new InternalErrorException();
            }
            if (!$command->execute([":title" => $title, ":tags" => $tags, ":description" => $description, ":id" => $post_id])) {
                $db->rollBack();
                throw new InternalErrorException();
            }

            $db->commit();
        } catch (PDOException) {
            try {
                $db->rollBack();
            } catch (PDOException) {
            }
            throw new InternalErrorException();
        }
    }


    public function deleteForumEntry(int $delete_post_id, int $user_id): void
    {
        try {
            $db = $this->connection;
            $db->beginTransaction();

            // Check if the user owns the forum entry
            $sql = "SELECT creator_id FROM forum WHERE id = :id LIMIT 1";
            $command = $db->prepare($sql);
            if (!$command) {
                $db->rollBack();
                throw new InternalErrorException();
            }
            if (!$command->execute([":id" => $delete_post_id])) {
                $db->rollBack();
                throw new InternalErrorException();
            }
            $result = $command->fetch();
            if (!$result) {
                $db->rollBack();
                throw new MissingEntryException();
            }

            // Check if the user_id matches the creator_id
            if ($result["creator_id"] !== $user_id) {
                $db->rollBack();
                throw new UnauthorizedAccessException();
            }

            // Checks if the post even exists
            $sql = "SELECT * FROM forum WHERE id=:id LIMIT 1";
            $command = $db->prepare($sql);
            if (!$command) {
                $db->rollBack();
                throw new InternalErrorException();
            }
            if (!$command->execute([":id" => $delete_post_id])) {
                $db->rollBack();
                throw new InternalErrorException();
            }
            $result = $command->fetchAll();
            if (empty($result)) {
                $db->rollBack();
                throw new MissingEntryException();
            }

            // Delete the comments with the associated post
            $sql = "DELETE FROM forum_comments WHERE post_id=:post_id";
            $command = $db->prepare($sql);
            if (!$command) {
                $db->rollBack();
                throw new InternalErrorException();
            }
            if (!$command->execute([":post_id" => $delete_post_id])) {
                $db->rollBack();
                throw new InternalErrorException();
            }

            // Delete the forum post itself
            $sql = "DELETE FROM forum WHERE id=:id";
            $command = $db->prepare($sql);
            if (!$command) {
                $db->rollBack();
                throw new InternalErrorException();
            }
            if (!$command->execute([":id" => $delete_post_id])) {
                $db->rollBack();
                throw new InternalErrorException();
            }

            $db->commit();
        } catch (PDOException) {
            try {
                $db->rollBack();
            } catch (PDOException) {
            }
            throw new InternalErrorException();
        }
    }


    public function getForumEntries(int $offset, int $limit): array
    {
        $db = $this->connection;
        // Gets all the forum entries and counts the corresponding forum comments of each entry (in order to set it)
        $sql = "SELECT f.*, COUNT(fc.comment_id) AS comment_count FROM forum f LEFT JOIN forum_comments fc ON f.id = fc.post_id GROUP BY f.id ORDER BY f.creation_time DESC LIMIT :start_index, :entries_per_page";
        $command = $db->prepare($sql); // prepared-statement to prevent sql injection

        // check if the preperation or execution failed
        if (!$command) {
            throw new InternalErrorException();
        }
        $params = [
            ':start_index' => $offset,
            ':entries_per_page' => $limit
        ];

        return $this->executeAndFetchAll($command, $params);
    }

    public function newForumComment(int $post_id, int $user_id, string $content): ?int
    {
        try {
            // Create a new forum comment and add it
            $db = $this->connection;
            $sql = "INSERT INTO forum_comments (post_id, user_id, content) VALUES (:post_id, :user_id, :content);";
            $command = $db->prepare($sql);
            if (!$command) {
                throw new InternalErrorException();
            }
            if (!$command->execute([":post_id" => $post_id, ":user_id" => $user_id, ":content" => $content])) {
                throw new InternalErrorException();
            }
            // Update the update_time of the corresponding post
            $sql = "UPDATE forum SET update_time = CURRENT_TIMESTAMP WHERE id = :id";
            $command = $db->prepare($sql);
            if (!$command) {
                throw new InternalErrorException();
            }
            if (!$command->execute([":id" => $post_id])) {
                throw new InternalErrorException();
            }
            return $post_id;
        } catch (PDOException) {
            throw new InternalErrorException();
        }
    }

    public function deleteForumComment(int $post_id, int $comment_id, int $user_id): void
    {
        try {
            $db = $this->connection;
            $db->beginTransaction();

            // Check if the user owns the forum comment
            $sql = "SELECT user_id FROM forum_comments WHERE post_id = :post_id AND comment_id = :comment_id LIMIT 1";
            $command = $db->prepare($sql);
            if (!$command) {
                $db->rollBack();
                throw new InternalErrorException();
            }
            if (!$command->execute([":post_id" => $post_id, ":comment_id" => $comment_id])) {
                $db->rollBack();
                throw new InternalErrorException();
            }
            $result = $command->fetch();
            if (!$result) {
                $db->rollBack();
                throw new MissingEntryException();
            }

            // Verify the user owns the comment
            if ($result["user_id"] !== $user_id) {
                $db->rollBack();
                throw new UnauthorizedAccessException();
            }
            // Delete the comments with the associated post
            $sql = "DELETE FROM forum_comments WHERE comment_id=:comment_id";
            $command = $db->prepare($sql);
            if (!$command) {
                $db->rollBack();
                throw new InternalErrorException();
            }
            if (!$command->execute([":comment_id" => $comment_id])) {
                $db->rollBack();
                throw new InternalErrorException();
            }

            $db->commit();
        } catch (PDOException) {
            try {
                $db->rollBack();
            } catch (PDOException) {
            }
            throw new InternalErrorException();
        }
    }

    public function updateForumComment(int $post_id, int $comment_id, int $user_id, string $description): void
    {
        try {
            $db = $this->connection;
            $db->beginTransaction();

            // Check if the user owns the forum comment
            $sql = "SELECT user_id FROM forum_comments WHERE post_id = :post_id AND comment_id = :comment_id LIMIT 1";
            $command = $db->prepare($sql);
            if (!$command) {
                $db->rollBack();
                throw new InternalErrorException();
            }
            if (!$command->execute([":post_id" => $post_id, ":comment_id" => $comment_id])) {
                $db->rollBack();
                throw new InternalErrorException();
            }
            $result = $command->fetch();
            if (!$result) {
                $db->rollBack();
                throw new MissingEntryException();
            }

            // Verify the user owns the comment
            if ($result["user_id"] !== $user_id) {
                $db->rollBack();
                throw new UnauthorizedAccessException();
            }

            $sql = "UPDATE forum_comments SET content = :content WHERE comment_id = :comment_id";
            $command = $db->prepare($sql);
            if (!$command) {
                $db->rollBack();
                throw new InternalErrorException();
            }
            if (!$command->execute([":comment_id" => $comment_id, ":content" => $description])) {
                $db->rollBack();
                throw new InternalErrorException();
            }

            $db->commit();
        } catch (PDOException) {
            try {
                $db->rollBack();
            } catch (PDOException) {
            }
            throw new InternalErrorException();
        }
    }

    // Analog to the getForumEntries function but uses the search parameters to create queries
    public function searchRequest(string $title, string $tags, string $sort_by, int $offset, int $limit): array
    {
        try {
            $db = $this->connection;
            $sql = "SELECT f.*, COUNT(fc.comment_id) AS comment_count FROM forum f LEFT JOIN forum_comments fc ON f.id = fc.post_id WHERE 1 = 1";

            if (!empty($title)) {
                $sql .= " AND f.title LIKE :title";
            }
            if (!empty($tags)) {
                $sql .= " AND f.tags LIKE :tags";
            }

            $sql .= " GROUP BY f.id";

            // Sort according to the selected sort_by variable
            if (!empty($sort_by)) {
                $sql .= match ($sort_by) {
                    'oldest' => " ORDER BY f.creation_time ASC",
                    'most_replies' => " ORDER BY comment_count DESC",
                    default => " ORDER BY f.creation_time DESC",
                };
            }

            $sql .= " LIMIT :start_index, :entries_per_page";

            $command = $db->prepare($sql); // prepared-statement to prevent sql injection
            if (!$command) {
                throw new InternalErrorException();
            }

            $params = [
                ':start_index' => $offset,
                ':entries_per_page' => $limit
            ];

            // Conditionally add title and tags to the parameters array
            if (!empty($title)) {
                $params[':title'] = '%' . $title . '%';
            }
            if (!empty($tags)) {
                $params[':tags'] = $tags;
            }

            $forum_entries = $this->executeAndFetchAll($command, $params);

            // Calculate total count using a subquery
            $sql_count = "SELECT COUNT(*) AS total_count FROM forum f WHERE 1 = 1";

            // Append conditions based on search parameters
            if (!empty($title)) {
                $sql_count .= " AND f.title LIKE :title_count";
            }
            if (!empty($tags)) {
                $sql_count .= " AND f.tags LIKE :tags_count";
            }

            // Prepare and execute count query
            $command_count = $db->prepare($sql_count);
            if (!$command_count) {
                throw new InternalErrorException();
            }

            // Bind parameters for count query
            $params_count = [];

            // Conditionally add title and tags to the count query parameters array
            if (!empty($title)) {
                $params_count[':title_count'] = '%' . $title . '%';
            }
            if (!empty($tags)) {
                $params_count[':tags_count'] = $tags;
            }

            // Execute the count query
            if (!$command_count->execute($params_count)) {
                throw new InternalErrorException();
            }

            // Fetch total count
            $result_count = $command_count->fetch(PDO::FETCH_ASSOC);
            $total_count = (int) $result_count['total_count'];

            return [
                'forum_entries' => $forum_entries,
                'total_count' => $total_count
            ];

        } catch (PDOException) {
            throw new InternalErrorException();
        }

    }

    /**
     * @throws InternalErrorException
     */
    public function getTotalForumEntriesCount(): int
    {
        try {
            $db = $this->connection;
            $sql = "SELECT COUNT(*) as total_entries FROM forum";
            $command = $db->prepare($sql);

            if (!$command) {
                throw new InternalErrorException();
            }

            $command->execute();
            $result = $command->fetch(PDO::FETCH_ASSOC);
            return (int) $result['total_entries'];
        } catch (PDOException) {
            throw new InternalErrorException();
        }
    }


    /**
     * Helper method for getForumEntries and searchRequest (including pagination)
     *
     * @throws InternalErrorException
     */
    private function executeAndFetchAll($command, $params): array
    {
        try {
            if (!$command->execute($params)) {
                throw new InternalErrorException();
            }
            $result = $command->fetchAll();

            $forum_entries = [];
            foreach ($result as $row) {
                $flickr_photo_id = $row["flickr_photo_id"] ?? '';
                $entry = new ForumEntry($row["id"], $row["creator_id"], $row["title"], $row["tags"], $row["description"], $row["creation_time"], $row["update_time"], $flickr_photo_id);
                $entry->setCommentCount($row["comment_count"]);
                $forum_entries[] = $entry;
            }
            return $forum_entries;
        } catch (PDOException) {
            throw new InternalErrorException();
        }
    }


    /**
     * Creates the default forum and forum_comments tables
     *
     * @return void
     */
    private function create(): void
    {
        $db = $this->connection;
        $db->exec("
                CREATE TABLE forum (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    creator_id INTEGER NOT NULL,
                    title TEXT,
                    tags TEXT,
                    description TEXT,
                    flickr_photo_id TEXT DEFAULT '',
                    creation_time DATETIME DEFAULT CURRENT_TIMESTAMP,
                    update_time DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (creator_id) REFERENCES  users(id)
                );");
        $db->exec("
                CREATE TABLE IF NOT EXISTS forum_comments (
                    comment_id INTEGER PRIMARY KEY AUTOINCREMENT,
                    post_id INTEGER NOT NULL,
                    user_id INTEGER NOT NULL,
                    content TEXT,
                    comment_creation_time DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (post_id) REFERENCES forum(id) ON DELETE CASCADE,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                );
            ");

        $db->exec("
                INSERT INTO forum (creator_id, title, tags, description) VALUES
                    ('1', 'Neue Spotting Location in EDDF entdeckt - kommt wer mit!?', '#PlaneSpottingSuche', 'Hallo Zusammen, ich bin letzten Samstag beim Gassigehen an der Ecke Sandweg links statt rechts abgebogen und konnte es kaum glauben: Nach 200 Metern befindet sich auf der rechten Seite ein Hügel, von dem man perfekte Aussicht auf Runway 25L hat. Hat jemand von euch Bock nächsten Samstag meinen Kampfhund und mich zu begleiten und coole Fotos zu schießen? GaLiGrü Bob')
                ;");
        $db->exec("
                INSERT INTO forum (creator_id, title, tags, description) VALUES
                    ('2', 'Test', '#Diskussion', 'Aerodynamic Description')
                ;");
        $db->exec("
                INSERT INTO forum (creator_id, title, tags, description) VALUES
                    ('3', 'Was ist der beste Planespotting Ort?', '#Diskussion', 'Was sind die schönsten Orte zum Planespotting?')
                ;");
        $db->exec("
                INSERT INTO forum_comments (post_id, user_id, content) VALUES
                    (1, 1, 'Das klingt super! Ich bin dabei. Freue mich auf Samstag.')
                ;");
        $db->exec("
                INSERT INTO forum_comments (post_id, user_id, content) VALUES
                    (1, 1, 'Oh ja! Coole Idee. Ich bin dabei. Würde meinen kleinen Mischling mitnehmen, wenn dein Kampfhund ihn nicht auffrisst hahahaha')
                ;");
        $db->exec("
                INSERT INTO forum_comments (post_id, user_id, content) VALUES
                    (3, 3, 'Test Kommentar')
                ;");
    }
}