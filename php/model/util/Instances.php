<?php

namespace model\util;

use ForumManagerSQLite;
use InternalErrorException;
use model\forum\ForumManagerInterface;
use model\posts\AbstractLikeManager;
use model\posts\PostManagerInterface;
use model\posts\SQLLikeManager;
use model\posts\SQLPostManager;
use model\user\UserManagerInterface;
use model\user\UserManagerPDOSQLite;

require_once __DIR__ . "/../posts/SQLPostManager.php";
require_once __DIR__ . "/../posts/PostManagerInterface.php";
require_once __DIR__ . "/../posts/DummyPostManager.php";
require_once __DIR__ . "/../user/UserManagerInterface.php";
require_once __DIR__ . "/../user/DummyUserManager.php";
require_once __DIR__ . "/../user/UserManagerPDOSQLite.php";
require_once __DIR__ . "/../forum/DummyForumManager.php";
require_once __DIR__ . "/../forum/ForumManagerInterface.php";
require_once __DIR__ . "/../forum/ForumManagerSQLite.php";
require_once __DIR__ . "/../posts/SQLLikeManager.php";

class Instances
{

    private static PostManagerInterface $postManager;
    private static UserManagerInterface $userManager;
    private static ForumManagerInterface $forumManager;
    private static AbstractLikeManager $likeManager;

    /**
     * @return PostManagerInterface
     * @throws InternalErrorException
     */
    public static function getPostManager(): PostManagerInterface
    {
        if (!isset(self::$postManager)) {
            //self::$postManager = new DummyPostManager();
            self::$postManager = new SQLPostManager();
        }
        return self::$postManager;
    }

    /**
     * @return UserManagerInterface
     * @throws InternalErrorException
     */
    public static function getUserManager(): UserManagerInterface
    {
        if (!isset(self::$userManager)) {
            //self::$userManager = new DummyUserManager();
            self::$userManager = new UserManagerPDOSQLite();
        }
        return self::$userManager;
    }

    /**
     * @return ForumManagerInterface
     * @throws InternalErrorException
     */
    public static function getForumManager(): ForumManagerInterface
    {
        if (!isset(self::$forumManager)) {
            //self::$forumManager = new DummyForumManager();
            self::$forumManager = new ForumManagerSQLite();
        }
        return self::$forumManager;
    }

    /**
     * @return AbstractLikeManager
     * @throws InternalErrorException
     */
    public static function getLikeManager(): AbstractLikeManager
    {
        if (!isset(self::$likeManager)) {
            self::$likeManager = new SQLLikeManager();
        }
        return self::$likeManager;
    }


}