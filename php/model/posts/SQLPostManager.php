<?php

namespace model\posts;

use DateTime;
use InternalErrorException;
use model\user\User;
use model\util\Instances;
use model\util\Util;
use PDO;
use PDOException;
use UserNotFoundException;

require_once __DIR__ . "/PostManagerInterface.php";
require_once __DIR__ . "/Post.php";
require_once __DIR__ . "/Comment.php";
require_once __DIR__ . "/PostNotFoundException.php";
require_once __DIR__ . "/ImageUploadHandler.php";
require_once __DIR__ . "/../util/Util.php";

class SQLPostManager implements PostManagerInterface
{
    private PDO $connection;

    public function __construct()
    {
        try {
            $this->connection = Util::getDBConnection();
        } catch (PDOException) {
            throw new InternalErrorException();
        }
        try {
            $needsTransaction = !$this->connection->inTransaction();
            if ($needsTransaction) $this->connection->beginTransaction();

            $sql = $this->connection->query("SELECT name FROM sqlite_master WHERE type='table' AND name='posts';");
            if ($sql->fetch() === false) {
                $this->connection->exec("
                    CREATE TABLE IF NOT EXISTS posts (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        author_id INTEGER NOT NULL,
                        title TEXT,
                        image_src TEXT,
                        date DATE,
                        reg_number TEXT,
                        manufacturer TEXT,
                        type TEXT,
                        airport TEXT,
                        camera TEXT,
                        lens TEXT,
                        iso TEXT,
                        aperture TEXT,
                        shutter TEXT,
                        creation_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (author_id) REFERENCES users(id)
                    );");
                $this->insertDummyPosts();
                $sql = $this->connection->query("SELECT name FROM sqlite_master WHERE type='table' AND name='posts_comments';");
                if ($sql->fetch() === false) {
                    $this->connection->exec("
                    CREATE TABLE IF NOT EXISTS posts_comments (
                        comment_id INTEGER PRIMARY KEY AUTOINCREMENT,
                        post_id INTEGER NOT NULL,
                        author_id INTEGER NOT NULL,
                        content TEXT,
                        creation_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
                        FOREIGN KEY (author_id) REFERENCES users(id)
                    );
                ");
                    $this->insertDummyComments();
                }
            }
            if ($needsTransaction) $this->connection->commit();
        } catch (PDOException) {
            if ($needsTransaction) try {
                $this->connection->rollBack();
            } catch (PDOException) {
                throw new InternalErrorException();
            }
            throw new InternalErrorException();
        }
        //Only there to assure tables will get generated
        Instances::getLikeManager();
    }

    private function insertDummyPosts(): void
    {
        $this->connection->exec("
            INSERT INTO posts (id, author_id, title, image_src, date, reg_number, manufacturer, type, airport, camera, lens, iso, aperture, shutter)
            VALUES 
            (1, 2, 'Condor A320 in Retro Livery am Flughafen EDDH', 'data/images/posts/1.jpg', '2024-02-10', 'D-AICH', 'Airbus', 'A320-212', 'EDDH', 'Sony A7C', '28-60mm', '640', 'f5.6', '1/1000'),
            (2, 1, 'Post 2', 'data/images/posts/2.jpg', '2024-02-11', 'D-AICH', 'Airbus', 'A320-212', 'EDDH', 'Sony A7C', '28-60mm', '640', 'f5.6', '1/1000'),
            (3, 3, 'Post 3', 'data/images/posts/3.jpg', '2024-05-09', 'D-AICH', 'Airbus', 'A320-212', 'EDDH', 'Sony A7C', '28-60mm', '640', 'f5.6', '1/1000');
        ");
    }

    public function insertDummyComments(): void
    {
        $this->connection->exec("
            INSERT INTO posts_comments (comment_id, post_id, author_id, content, creation_timestamp)
            VALUES 
            (1, 1, 3, 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi. Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi. Nam liber tempor cum soluta nobis eleifend option congue nihil imperdiet doming id quod mazim placerat facer', datetime('now')),
            (2, 1, 1, 'Tolles Bild!', datetime('now')),
            (3, 2, 3, 'Sehr beeindruckend!', datetime('now')),
            (4, 1, 2, 'Interessante Perspektive.', datetime('now')),
            (5, 2, 2, 'Test <br>', datetime('now')),
            (6, 3, 2, 'Super Bild', datetime('now'));
        ");
    }

    public function hasPost(int $id): bool
    {
        try {
            $stmt = $this->connection->prepare("SELECT COUNT(*) FROM posts WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException) {
            throw new InternalErrorException();
        }
    }

    public function getPostById(int $id): Post
    {
        try {
            $this->connection->beginTransaction();
            $stmt = $this->connection->prepare("SELECT * FROM posts WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $post_data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($post_data === false) {
                throw new PostNotFoundException();
            }

            $post_data["likes"] = $this->getPostLikes($id);
            $post_data["date"] = DateTime::createFromFormat("Y-m-d", $post_data["date"]);
            $this->connection->commit();
            try {
                return $this->getPostBySQLData($post_data);
            } catch (UserNotFoundException) {
                throw new PostNotFoundException();
            }
        } catch (PDOException) {
            try {
                $this->connection->rollBack();
            } catch (PDOException) {
                throw new InternalErrorException();
            }
            throw new InternalErrorException();
        } catch (InternalErrorException) {
            throw new InternalErrorException();
        }
    }

    /**
     * @param int $postId
     * @return int
     * @throws PDOException
     */
    private function getPostLikes(int $postId): int
    {
        $stmt = $this->connection->prepare("SELECT COUNT(*) FROM posts_likes WHERE post_id = :post_id");
        $stmt->execute([
            ':post_id' => $postId
        ]);
        return $stmt->fetchColumn();
    }

    public function savePost(Post $post): void
    {
        try {
            $stmt = $this->connection->prepare("
                UPDATE posts SET 
                    title = :title,
                    date = :date,
                    reg_number = :reg_number,
                    manufacturer = :manufacturer,
                    type = :type,
                    airport = :airport,
                    camera = :camera,
                    lens = :lens,
                    iso = :iso,
                    aperture = :aperture,
                    shutter = :shutter
                WHERE id = :id
            ");

            $stmt->execute([
                ':id' => $post->getId(),
                ':title' => $post->getTitle(),
                ':date' => $post->getDate()->format("Y-m-d"),
                ':reg_number' => $post->getRegNumber(),
                ':manufacturer' => $post->getManufacturer(),
                ':type' => $post->getType(),
                ':airport' => $post->getAirport(),
                ':camera' => $post->getCamera(),
                ':lens' => $post->getLens(),
                ':iso' => $post->getIso(),
                ':aperture' => $post->getAperture(),
                ':shutter' => $post->getShutter()
            ]);
        } catch (PDOException) {
            throw new InternalErrorException();
        }
    }

    public function createPost(array $post_data, array $imgData): Post
    {
        try {
            $this->connection->beginTransaction();

            $stmt = $this->connection->prepare("
                INSERT INTO posts (author_id, title, date, reg_number, manufacturer, type, airport, camera, lens, iso, aperture, shutter)
                VALUES (:author_id, :title, :date, :reg_number, :manufacturer, :type, :airport, :camera, :lens, :iso, :aperture, :shutter)
            ");

            $stmt->execute([
                ':author_id' => $post_data['user_id'],
                ':title' => $post_data['title'],
                ':date' => $post_data['date']->format("Y-m-d"),
                ':reg_number' => $post_data['reg_number'],
                ':manufacturer' => $post_data['manufacturer'],
                ':type' => $post_data['type'],
                ':airport' => $post_data['airport'],
                ':camera' => $post_data['camera'],
                ':lens' => $post_data['lens'],
                ':iso' => $post_data['iso'],
                ':aperture' => $post_data['aperture'],
                ':shutter' => $post_data['shutter']
            ]);

            $post_id = $this->connection->lastInsertId();

            $post_data['id'] = $post_id;

            $filenamePath = $imgData["upload_directory"] . $post_id . '.' . $imgData["extension"];
            $imgHandler = new ImageUploadHandler();
            $success = $imgHandler->setupImage($filenamePath, $imgData["img_type"], $imgData["temp_file_path"]);
            if (!$success) {
                $this->connection->rollBack();
                throw new InternalErrorException();
            }

            $stmt = $this->connection->prepare("
            UPDATE posts SET image_src = :image_src WHERE id = :id
        ");

            $newImagePath = "data/images/posts/" . $post_id . "." . $imgData["extension"];
            $stmt->execute([
                ':image_src' => $newImagePath,
                ':id' => $post_id
            ]);
            $post_data["image_src"] = $newImagePath;
            $user = Instances::getUserManager()->getUserById($post_data["user_id"]);
            unset($post_data["user_id"]);
            $post_data["user"] = $user;
            $this->connection->commit();
            return new Post($post_data);
        } catch (PDOException) {
            try {
                $this->connection->rollBack();
            } catch (PDOException) {
                throw new InternalErrorException();
            }
            throw new InternalErrorException();
        }
    }


    public function deletePost(Post $post): void
    {
        $this->deletePostById($post->getId());
    }

    public function deletePostById($id): void
    {
        try {
            $this->connection->beginTransaction();
            $stmt = $this->connection->prepare("DELETE FROM posts WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $stmt = $this->connection->prepare("DELETE FROM posts_comments WHERE post_id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $stmt = $this->connection->prepare("DELETE FROM posts_likes WHERE post_id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $this->connection->commit();
        } catch (PDOException) {
            try {
                $this->connection->rollBack();
            } catch (PDOException) {
                throw new InternalErrorException();
            }
            throw new InternalErrorException();
        }
    }

    public function getPostsDesc(int $limit): array
    {
        try {
            $this->connection->beginTransaction();
            $stmt = $this->connection->prepare("SELECT * FROM posts ORDER BY id DESC LIMIT :limit");
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $posts_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $posts = [];
            foreach ($posts_data as $post_data) {
                $post_data["likes"] = $this->getPostLikes($post_data["id"]);
                $post_data["date"] = DateTime::createFromFormat("Y-m-d", $post_data["date"]);
                try {
                    $posts[] = $this->getPostBySQLData($post_data);
                } catch (UserNotFoundException) {
                    continue;
                }
            }

            $this->connection->commit();
            return $posts;
        } catch (PDOException) {
            try {
                $this->connection->rollBack();
            } catch (PDOException) {
                throw new InternalErrorException();
            }
            throw new InternalErrorException();
        } catch (InternalErrorException) {
            throw new InternalErrorException();
        }
    }

    public function getPostsDescWithId(int $offset, int $limit): array
    {
        try {
            $this->connection->beginTransaction();
            $stmt = $this->connection->prepare("SELECT * FROM posts WHERE id < :id ORDER BY id DESC LIMIT :limit");
            $stmt->bindParam(':id', $offset, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $posts_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $posts = [];
            foreach ($posts_data as $post_data) {
                $post_data["likes"] = $this->getPostLikes($post_data["id"]);
                $post_data["date"] = DateTime::createFromFormat("Y-m-d", $post_data["date"]);
                try {
                    $posts[] = $this->getPostBySQLData($post_data);
                } catch (UserNotFoundException) {
                    continue;
                }
            }

            $this->connection->commit();
            return $posts;
        } catch (PDOException) {
            try {
                $this->connection->rollBack();
            } catch (PDOException) {
                throw new InternalErrorException();
            }
            throw new InternalErrorException();
        }
    }


    public function deleteComment(Post $post, int $commentId): int
    {
        try {
            $stmt = $this->connection->prepare("DELETE FROM posts_comments WHERE comment_id = :comment_id AND post_id = :post_id");
            $stmt->bindParam(':comment_id', $commentId, PDO::PARAM_INT);
            $id = $post->getId();
            $stmt->bindParam(':post_id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount();
        } catch (PDOException) {
            throw new InternalErrorException();
        }
    }


    public function addComment(Post $post, User $author, string $content): Comment
    {
        try {
            $stmt = $this->connection->prepare("
                INSERT INTO posts_comments (post_id, author_id, content)
                VALUES (:post_id, :author_id, :content)
            ");

            $stmt->execute([
                ':post_id' => $post->getId(),
                ':author_id' => $author->getId(),
                ':content' => $content
            ]);

            return new Comment($this->connection->lastInsertId(), $author, $content);
        } catch (PDOException) {
            throw new InternalErrorException();
        }
    }

    /**
     * @throws InternalErrorException
     * @throws UserNotFoundException
     */
    private function getPostBySQLData(array $post_data): Post
    {
        $post_data['user'] = Instances::getUserManager()->getUserById($post_data['author_id']);
        unset($post_data['author_id']);
        $post_data['comments'] = $this->getCommentsForPost($post_data['id']);
        return new Post($post_data);
    }

    /**
     * @throws InternalErrorException
     */
    private function getCommentsForPost(int $post_id): array
    {
        try {
            $stmt = $this->connection->prepare("SELECT * FROM posts_comments WHERE post_id = :post_id");
            $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
            $stmt->execute();
            $comments_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $comments = [];
            foreach ($comments_data as $comment_data) {
                try {
                    $user = Instances::getUserManager()->getUserById($comment_data['author_id']);
                } catch (UserNotFoundException) {
                    continue;
                }
                $comments[] = new Comment($comment_data['comment_id'], $user, $comment_data['content']);
            }
            return $comments;
        } catch (PDOException) {
            throw new InternalErrorException();
        }
    }

    public function deleteAllPostsFromUserById(int $userId): void
    {
        try {
            $needsTransaction = !$this->connection->inTransaction();
            if ($needsTransaction) $this->connection->beginTransaction();
            $stmt = $this->connection->prepare("DELETE FROM posts WHERE author_id = :id");
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $this->deleteAllCommentsFromUserById($userId);
            if ($needsTransaction) $this->connection->commit();
        } catch (PDOException) {
            try {
                if ($needsTransaction) $this->connection->rollBack();
            } catch (PDOException) {
                throw new InternalErrorException();
            }
            throw new InternalErrorException();
        }
    }

    public function deleteAllCommentsFromUserById(int $userId): void
    {
        try {
            $stmt = $this->connection->prepare("DELETE FROM posts_comments WHERE author_id = :id");
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException) {
            throw new InternalErrorException();
        }
    }

}