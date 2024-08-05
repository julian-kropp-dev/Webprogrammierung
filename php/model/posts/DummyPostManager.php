<?php

namespace model\posts;

use InternalErrorException;
use model\util\Instances;
use model\user\User;
use DateTime;
use UserNotFoundException;

require_once __DIR__ . "/PostManagerInterface.php";
require_once __DIR__ . "/Post.php";
require_once __DIR__ . "/Comment.php";
require_once __DIR__ . "/PostNotFoundException.php";

class DummyPostManager implements PostManagerInterface
{
    private array $posts;

    /**
     * @throws InternalErrorException
     * @throws UserNotFoundException
     */
    public function __construct()
    {
        $this->posts = $this->generateDummyPosts();
    }

    /**
     * @throws InternalErrorException
     * @throws UserNotFoundException
     */
    private function generateDummyPosts(): array
    {
        $userManager = Instances::getUserManager();
        $data = array(
            array(
                "user" => $userManager->getUserById(2),
                "title" => "Condor A320 in Retro Livery am Flughafen EDDH",
                "image_src" => "data/images/posts/1.jpg",
                "date" => DateTime::createFromFormat("d.m.Y", "10.02.2024"),
                "reg_number" => "D-AICH",
                "manufacturer" => "Airbus",
                "type" => "A320-212",
                "airport" => "EDDH",
                "camera" => "Sony A7C",
                "lens" => "28-60mm",
                "iso" => "640",
                "aperture" => "f5.6",
                "shutter" => "1/1000",
                "likes" => 200,
                "id" => 1,
                "comments" => array(
                    new Comment(1, $userManager->getUserById(3), "Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum."),
                    new Comment(2, $userManager->getUserById(1), "Tolles Bild!"),
                    new Comment(3, $userManager->getUserById(3), "Sehr beeindruckend!"),
                    new Comment(4, $userManager->getUserById(2), "Interessante Perspektive.")
                )
            ),
            array(
                "user" => $userManager->getUserById(1),
                "title" => "Post 2",
                "image_src" => "data/images/posts/2.jpg",
                "date" => DateTime::createFromFormat("d.m.Y", "11.02.2024"),
                "reg_number" => "D-AICH",
                "manufacturer" => "Airbus",
                "type" => "A320-212",
                "airport" => "EDDH",
                "camera" => "Sony A7C",
                "lens" => "28-60mm",
                "iso" => "640",
                "aperture" => "f5.6",
                "shutter" => "1/1000",
                "likes" => 150,
                "id" => 2,
                "comments" => array(
                    new Comment(5, $userManager->getUserById(2), "Test <br>"),
                    new Comment(6, $userManager->getUserById(3), "Sehr schön!")
                )
            ),
            array(
                "user" => $userManager->getUserById(3),
                "title" => "Post 3",
                "image_src" => "data/images/posts/3.jpg",
                "date" => DateTime::createFromFormat("d.m.Y", "09.05.2024"),
                "reg_number" => "D-AICH",
                "manufacturer" => "Airbus",
                "type" => "A320-212",
                "airport" => "EDDH",
                "camera" => "Sony A7C",
                "lens" => "28-60mm",
                "iso" => "640",
                "aperture" => "f5.6",
                "shutter" => "1/1000",
                "likes" => 100,
                "id" => 3,
                "comments" => array(
                    new Comment(7, $userManager->getUserById(2), "Super Bild")
                )
            )
        );

        $posts = [];
        foreach ($data as $postData) {
            $posts[$postData["id"]] = new Post($postData);
        }

        return $posts;
    }

    public function hasPost(int $id): bool
    {
        return isset($this->posts[$id]);
    }

    public function getPostById(int $id): Post
    {
        if (!$this->hasPost($id)) {
            throw new PostNotFoundException();
        }
        return $this->posts[$id];
    }

    public function savePost(Post $post): void
    {
        if (!$this->hasPost($post->getId())) {
            throw new PostNotFoundException();
        }
        $this->posts[$post->getId()] = $post;
    }

    public function createPost(array $post_data, array $imgData): Post
    {
        $post_data['id'] = count($this->posts) + 1;
        $post_data['image_src'] = "data/images/posts/" . $post_data['id'] . "." . $imgData["extension"];
        $post = new Post($post_data);
        $this->posts[$post->getId()] = $post;
        return $post;
    }

    public function deletePost(Post $post): void
    {
        $this->deletePostById($post->getId());
    }

    public function deletePostById(int $id): void
    {
        if (!$this->hasPost($id)) {
            throw new PostNotFoundException();
        }
        unset($this->posts[$id]);
    }

    public function getPostsDesc(int $limit): array
    {
        return array_slice(array_reverse($this->posts), 0, $limit);
    }

    public function getPostsDescWithId(int $offset, int $limit): array
    {
        $posts = array_filter($this->posts, fn($post) => $post->getId() < $offset);
        return array_slice(array_reverse($posts), 0, $limit);
    }

    public function deleteComment(Post $post, int $commentId): int
    {
        foreach ($post->getComments() as $key => $comment) {
            if ($comment->getId() === $commentId) {
                unset($post->getComments()[$key]);
                return 1;
            }
        }
        return 0;
    }

    public function addComment(Post $post, User $author, string $content): Comment
    {
        $commentId = count($post->getComments()) + 1;
        $comment = new Comment($commentId, $author, $content);
        $post->getComments()[] = $comment;
        return $comment;
    }

    public function deleteAllPostsFromUserById(int $userId): void
    {
        foreach ($this->posts as $id => $post) {
            if ($post->getUser()->getId() === $userId) {
                unset($this->posts[$id]);
            }
        }
    }

    public function deleteAllCommentsFromUserById(int $userId): void
    {
        foreach ($this->posts as $post) {
            foreach ($post->getComments() as $key => $comment) {
                if ($comment->getAuthor()->getId() === $userId) {
                    unset($post->getComments()[$key]);
                }
            }
        }
    }
}
