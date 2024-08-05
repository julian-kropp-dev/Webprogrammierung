<?php

use model\forum\ForumComment;

require_once __DIR__ . "/../user/UserManagerInterface.php";
require_once __DIR__ . "/ForumComment.php";

class ForumEntry
{
    private string $id;
    private string $title;
    private string $tags;
    private string $description;
    private int $creator_id;
    private string $creation_time;
    private string $update_time;
    private array $comments = [];
    private int $comment_count = 0;
    private string $flickr_id = "";

    public function __construct(string $id, int $creator_id, string $title, string $tags, string $description, string $creation_time, string $update_time, string $flickr_id)
    {
        $this->id = $id;
        $this->creator_id = $creator_id;
        $this->title = $title;
        $this->tags = $tags;
        $this->description = $description;
        $this->creation_time = $creation_time;
        $this->update_time = $update_time;
        $this->flickr_id = $flickr_id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCreator(): int
    {
        return $this->creator_id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getTags(): string
    {
        return $this->tags;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCreationTime(): string
    {
        return $this->creation_time;
    }

    public function getUpdateTime(): string
    {
        return $this->update_time;
    }

    public function getComments(): array
    {
        return $this->comments;
    }

    public function setComments(array $comments): void
    {
        $this->comments = $comments;
    }

    public function getCommentCount(): int
    {
        return $this->comment_count;
    }

    public function setCommentCount(int $comment_count): void
    {
        $this->comment_count = $comment_count;
    }

    public function getFlickrId(): string
    {
        return $this->flickr_id;
    }

    public function getCommentById(int $id): ?ForumComment
    {
        foreach ($this->comments as $comment) {
            if ($comment->getCommentId() == $id) {
                return $comment;
            }
        }
        return null;
    }

    // following methods are only for the DummyForumManager
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function setTags(string $tags): void
    {
        $this->tags = $tags;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

}

