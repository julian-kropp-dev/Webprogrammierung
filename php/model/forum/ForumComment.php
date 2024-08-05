<?php

namespace model\forum;

class ForumComment
{
    private int $comment_id;
    private int $post_id;
    private int $user_id;
    private string $content;
    private string $creation_time;

    /**
     * @param int $comment_id
     * @param int $post_id
     * @param int $user_id
     * @param string $content
     * @param string $creation_time
     */
    public function __construct(int $comment_id, int $post_id, int $user_id, string $content, string $creation_time)
    {
        $this->comment_id = $comment_id;
        $this->post_id = $post_id;
        $this->user_id = $user_id;
        $this->content = $content;
        $this->creation_time = $creation_time;
    }

    public function getCommentId(): int
    {
        return $this->comment_id;
    }

    public function getPostId(): int
    {
        return $this->post_id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getCreationTime(): string
    {
        return $this->creation_time;
    }
}