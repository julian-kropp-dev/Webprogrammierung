<?php

namespace model\posts;

use InternalErrorException;
use model\util\Instances;
use model\user\User;

require_once __DIR__ . "/AbstractLikeManager.php";

class DummyLikeManager extends AbstractLikeManager
{

    private array $data;

    public function __construct()
    {
        $this->data = array (
            1 => array(1, 2),
            2 => array(1),
            3 => array(),
        );
    }

    public function hasLikedById(int $user_id, int $post_id): bool
    {
        if(!isset($this->data[$post_id])) return false;
        $likedUsers = $this->data[$post_id];
        if (in_array($user_id, $likedUsers)) {
            return true;
        }
        return false;
    }

    public function likeById(int $user_id, int $post_id)
    {
        if (isset($this->data[$post_id])) {
            $likeArray =& $this->data[$post_id];
            if (in_array($user_id, $likeArray)) return;
            $likeArray[] = $user_id;
        }else{
            $this->data[$post_id] = array($user_id);
        }
    }


    public function unlikeById(int $user_id, int $post_id)
    {
        if (isset($this->data[$post_id])) {
            $likeArray =& $this->data[$post_id];
            $index = array_search(2, $likeArray);
            if (!$index) return;
            unset($likeArray[$index]);
        }
    }


}