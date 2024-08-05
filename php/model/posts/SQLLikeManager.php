<?php

namespace model\posts;

use InternalErrorException;
use model\util\Instances;
use model\util\Util;
use PDO;
use PDOException;

require_once __DIR__ . "/AbstractLikeManager.php";
require_once __DIR__ . "/../util/Instances.php";

class SQLLikeManager extends AbstractLikeManager
{


    private PDO $connection;

    /**
     * @throws InternalErrorException
     */
    public function __construct()
    {
        parent::__construct();

        try {
            $this->connection = Util::getDBConnection();
        } catch (PDOException) {
            throw new InternalErrorException();
        }
        try {
            $needsTransaction = !$this->connection->inTransaction();
            if ($needsTransaction) $this->connection->beginTransaction();
            $sql = $this->connection->query("SELECT name FROM sqlite_master WHERE type='table' AND name='posts_likes';");
            if ($sql->fetch() === false) {
                $this->connection->exec("
                    CREATE TABLE posts_likes (
                        post_id INTEGER NOT NULL,
                        user_id INTEGER NOT NULL,
                        FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
                        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                    );
                ");




                $postLikes = [1 => [1, 2], 2 => [1], 3 => [2]];

                $checkPostStmt = $this->connection->prepare("SELECT id FROM posts WHERE id = :id");


                $insertLikeStmt = $this->connection->prepare("INSERT INTO posts_likes (post_id, user_id) VALUES (:post_id, :user_id)");

                foreach ($postLikes as $postId => $likeArray) {
                    $checkPostStmt->execute([':id' => $postId]);
                    $postExists = $checkPostStmt->fetchColumn();

                    $userManager = Instances::getUserManager();
                    if ($postExists) {
                        foreach ($likeArray as $userId) {
                            if ($userManager->isUserExisting($userId)) {
                                $insertLikeStmt->execute([':post_id' => $postId, ':user_id' => $userId]);
                            }
                        }
                    }
                }
            }






            if ($needsTransaction) $this->connection->commit();
        } catch (PDOException $e) {
            try {
                if ($needsTransaction)  $this->connection->rollBack();
            } catch (PDOException $e) {
            }

            throw new InternalErrorException();
        }

    }

    public function hasLikedById(int $user_id, int $post_id): bool {
        try {
            $stmt = $this->connection->prepare("SELECT COUNT(*) FROM posts_likes WHERE user_id = :user_id AND post_id = :post_id");
            $stmt->execute([
                ':user_id' => $user_id,
                ':post_id' => $post_id
            ]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException) {
            throw new InternalErrorException();
        }
    }


    public function likeById(int $user_id, int $post_id) {
        try {
            $this->connection->beginTransaction();
            if (!$this->hasLikedById($user_id, $post_id)) {
                $stmt = $this->connection->prepare("INSERT INTO posts_likes (user_id, post_id) VALUES (:user_id, :post_id)");
                $stmt->execute([
                    ':user_id' => $user_id,
                    ':post_id' => $post_id
                ]);
            }
            $this->connection->commit();
        } catch (PDOException) {
            try {
                $this->connection->rollBack();
            } catch (PDOException) {
            }
            throw new InternalErrorException();
        }
    }

    public function unlikeById(int $user_id, int $post_id) {
        try {
            $stmt = $this->connection->prepare("DELETE FROM posts_likes WHERE user_id = :user_id AND post_id = :post_id");
            $stmt->execute([
                ':user_id' => $user_id,
                ':post_id' => $post_id
            ]);
        } catch (PDOException) {
            throw new InternalErrorException();
        }
    }


}