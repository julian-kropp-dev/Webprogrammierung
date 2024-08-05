<?php

namespace model\posts;

 use InternalErrorException;
 use model\user\User;

 abstract class AbstractLikeManager
{

     /**
      * @throws InternalErrorException
      */
     public function __construct()
     {
     }

     /**
      * @param User $user
      * @param Post $post
      * @return bool
      * @throws InternalErrorException
      */
     public function hasLiked(User $user, Post $post) : bool
     {
         return $this->hasLikedById($user->getId(), $post->getId());
     }
     /**
      * @param int $user_id
      * @param int $post_id
      * @return bool
      * @throws InternalErrorException
      */
     public abstract function hasLikedById(int $user_id, int $post_id) : bool;

     /**
      * @param User $user
      * @param Post $post
      * @return void
      * @throws PostNotFoundException
      * @throws InternalErrorException
      */
    public function like(User $user, Post $post) {
        $this->likeById($user->getId(), $post->getId());
    }

     /**
      * @param int $user_id
      * @param int $post_id
      * @return mixed
      * @throws PostNotFoundException
      * @throws InternalErrorException
      */
    public abstract function likeById(int $user_id, int $post_id);

     /**
      * @param User $user
      * @param Post $post
      * @return void
      * @throws PostNotFoundException
      * @throws InternalErrorException
      */
    public function unlike(User $user, Post $post){
        $this->unlikeById($user->getId(), $post->getId());
    }

     /**
      * @param int $user_id
      * @param int $post_id
      * @return void
      * @throws PostNotFoundException
      * @throws InternalErrorException
      */
    public abstract function unlikeById(int $user_id, int $post_id);
}