<?php

namespace model\posts;

use exceptions\PostAlreadyExistsException;
use InternalErrorException;
use model\user\User;
use UserNotFoundException;

include_once "Post.php";

interface PostManagerInterface
{

    /**
     * @throws InternalErrorException
     */
    public function __construct();

    /**
     * @param int $id of the post
     * @return bool if post is existing
     * @throws InternalErrorException
     */
    public function hasPost(int $id) : bool;

    /**
     * @param int $id of the post
     * @return Post
     * @throws PostNotFoundException if post is not found
     * @throws InternalErrorException
     */
    public function getPostById(int $id) : Post;

    /**
     * @param Post $post
     * @return void
     * @throws InternalErrorException
     */
    public function savePost(Post $post): void;

    /**
     * @param array $post_data args are: user_id, title, image_src, date, reg_number, manufacturer,
     * type, airport, camera, lens, iso, aperture, shutter
     * @param array $imgData
     * @return Post
     * @throws PostAlreadyExistsException
     * @throws UserNotFoundException
     * @throws InternalErrorException
     */
    public function createPost(array $post_data, array $imgData) : Post;

    /**
     * @param Post $post
     * @throws InternalErrorException
     *
     */
    public function deletePost(Post $post): void;

    /**
     * @param int $id
     * @return void
     * @throws InternalErrorException
     */
    public function deletePostById(int $id) : void;

    /**
     * @param int $limit
     * @return array
     * @throws InternalErrorException
     */
    public function getPostsDesc(int $limit) : array;
    /**
     * @param int $offset the post id from which post should be returned
     * @param int $limit
     * @return array
     * @throws InternalErrorException
     */
    public function getPostsDescWithId(int $offset, int $limit) : array;

    /**
     * @param Post $post
     * @param int $commentId
     * @return int
     * @throws InternalErrorException
     */
    function deleteComment(Post $post, int $commentId) : int;

    /**
     * @param Post $post
     * @param User $author
     * @param String $content
     * @return Comment
     * @throws InternalErrorException
     */
    function addComment(Post $post, User $author, String $content) : Comment;

    /**
     * @param int $userId
     * @return void
     * @throws InternalErrorException
     */
    public function deleteAllPostsFromUserById(int $userId): void;    /**
     * @param int $userId
     * @return void
     * @throws InternalErrorException
     */
    public function deleteAllCommentsFromUserById(int $userId): void;

}


