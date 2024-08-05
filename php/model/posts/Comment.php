<?php

namespace model\posts;

use model\user\User;

class Comment
{
    private int $id;
    private User $user;
    private string $content;

    /**
     * @param User $user
     * @param string $content
     */
    function __construct(int $id, User $user, string $content)
    {
        $this->id = $id;
        $this->user = $user;
        $this->content = $content;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getId(): int
    {
        return $this->id;
    }




}