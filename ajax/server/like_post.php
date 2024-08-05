<?php
require_once __DIR__ . "/../../php/model/util/Util.php";
require_once __DIR__ . "/../../php/model/util/Instances.php";
require_once __DIR__ . "/../../php/model/posts/PostNotFoundException.php";
require_once __DIR__ . "/../../php/model/posts/AbstractLikeManager.php";

use model\posts\PostNotFoundException;
use model\util\Instances;
use model\util\Util;
if (session_status() != PHP_SESSION_ACTIVE) session_start();
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Unknown error'];

if (isset($_POST["post_id"])) {
    if (!is_numeric($_POST["post_id"])) {
        $response['message'] = 'Invalid post ID';
        echo json_encode($response);
        return;
    }
    if (!Util::isLoggedIn()) {
        $response['message'] = 'User not logged in';
        echo json_encode($response);
        return;
    }

    $postId = intval($_POST["post_id"]);
    $user = Util::getUserFromSession();
    if ($user == null) {
        $response['message'] = 'User not found in session';
        echo json_encode($response);
        return;
    }

    $userId = $user->getId();
    try {
        $likeManager = Instances::getLikeManager();
        $postManger = Instances::getPostManager();
        if ($likeManager->hasLikedById($userId, $postId)) {
            $likeManager->unlikeById($userId, $postId);
            $response = ['status' => 'success', 'liked' => false];
        } else {
            $likeManager->likeById($userId, $postId);
            $response = ['status' => 'success', 'liked' => true];
        }
        $response["like_count"] = $postManger->getPostById($postId)->getLikes();
    } catch (InternalErrorException $e) {
        $response['message'] = 'Internal server error';
        echo json_encode($response);
        return;
    } catch (PostNotFoundException $e) {
        $response['message'] = 'Post not found';
        echo json_encode($response);
        return;
    }
} else {
    $response['message'] = 'Post ID not set';
}

echo json_encode($response);
