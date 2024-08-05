<?php

use model\util\Instances;

require_once __DIR__ . "/../../php/model/util/Instances.php";
require_once __DIR__ . "/../../php/model/util/Util.php";
require_once __DIR__ . "/../../php/model/posts/PostManagerInterface.php";

if (!isset($_GET['offset']) || !is_numeric($_GET['offset'])) {
    echo json_encode([]);
    exit;
}

$offset = intval($_GET['offset']);

$posts = [];
try {
    $posts = Instances::getPostManager()->getPostsDescWithId($offset, 12);
} catch (InternalErrorException $e) {
}

$response = [];
foreach ($posts as $post) {
    $response[] = [
        'title' => $post->getTitle(),
        'airport' => $post->getAirport(),
        'username' => $post->getUser()->getUsername(),
        'profile_photo' => $post->getUser()->getProfilePhoto(),
        'image_src' => $post->getImageSrc(),
        'user_id' => $post->getUser()->getId(),
        'post_id' => $post->getId()
    ];
}

echo json_encode($response);
