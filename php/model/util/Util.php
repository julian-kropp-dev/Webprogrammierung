<?php

namespace model\util;

use DateTime;
use InternalErrorException;
use model\posts\PostNotFoundException;
use model\user\User;
use PDO;
use PDOException;
use UserNotFoundException;

require_once __DIR__ . "/Instances.php";

class Util
{
    private static PDO $pdo;

    public static function isPostAuthor($post_id): bool
    {


        global $post;
        try {
            $post = Instances::getPostManager()->getPostById($post_id);
        } catch (PostNotFoundException) {
            return false;
        } catch (InternalErrorException) {
            //TOdo better handling, maybe let the method trow it
            return false;
        }

        $author_id = $post->getUser()->getId();
        if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id']))
            return false;
        $user_id = $_SESSION['user_id'];

        return $user_id == $author_id;

    }

    public static function isPostCommentAuthor($post_id, $comment_id)
    {
        global $post;
        try {
            $post = Instances::getPostManager()->getPostById($post_id);
        } catch (PostNotFoundException) {
            return false;
        } catch (InternalErrorException $e) {
            //TOdo better handling, maybe let the method trow it
            return false;
        }

        foreach ($post->getComments() as $comment) {
            if ($comment->getId() == $comment_id) {
                if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id']))
                    return false;
                $user_id = $_SESSION['user_id'];
                return $comment->getUser()->getId() == $user_id;
            }
        }
        return false;
    }

    public static function isLoggedIn(): bool
    {
        if (!isset($_SESSION['logged']))
            return false;
        return $_SESSION['logged'] === true;

    }

    /**
     * @return User|null
     */
    public static function getUserFromSession(): ?User
    {
        if (!self::isLoggedIn())
            return null;
        if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id']))
            return null;
        $user_id = $_SESSION['user_id'];

        $user = null;
        try {
            $user = Instances::getUserManager()->getUserById($user_id);
        } catch (InternalErrorException | UserNotFoundException $e) {
            session_destroy();
            session_unset();
        }
        return $user;
    }

    /**
     * Gets the profile photo of the user with the given id.
     *
     * @param int $id The ID of the user whose username is being retrieved.
     * @return string The path to the profile photo.
     * @throws InternalErrorException
     * @throws UserNotFoundException
     */
    public static function getProfilePhotoById(int $id): string
    {
        return Instances::getUserManager()->getUserById($id)->getProfilePhoto();
    }

    /**
     * Gets the username of the user with the given id.
     *
     * @param int $id The id of the user whose username is being retrieved.
     * @return string The username of the id.
     * @throws InternalErrorException
     * @throws UserNotFoundException
     */
    public static function getUsernameById(int $id): string
    {
        return Instances::getUserManager()->getUserById($id)->getUsername();
    }

    /**
     * @throws PDOException
     */
    public static function getDBConnection(): PDO
    {
        if (!isset(self::$pdo)) {
            $databaseFilePath = __DIR__ . '/../../../data/database/database.db';

            $directoryPath = dirname($databaseFilePath);

            if (!is_dir($directoryPath)) {
                mkdir($directoryPath, 0777, true);
            }
            $user = 'root';
            $pw = null;
            $dsn = 'sqlite:' . $databaseFilePath;
            self::$pdo = new PDO($dsn, $user, $pw);
        }
        return self::$pdo;


    }

    public static function validateDate($date, $format = 'Y-m-d'): bool
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}
