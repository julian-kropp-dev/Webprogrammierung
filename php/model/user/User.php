<?php
namespace model\user;

class User
{
    private int $id;
    private string $email;
    private string $password;
    private string $username;
    private string $biography;
    private string $profilePhoto;

    public function __construct(int $id, string $email, string $password, string $username, string $biography, string $profilePhoto)
    {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
        $this->username = $username;
        $this->biography = $biography;
        $this->profilePhoto = $profilePhoto;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getBiography(): string
    {
        return $this->biography;
    }

    public function getProfilePhoto(): string
    {
        return $this->profilePhoto;
    }

    public function setProfilePhoto(string $profilePhoto): void
    {
        $this->profilePhoto = $profilePhoto;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }



    // Hilfsfunktion, um Umbrüche in der Biografie richtig darzustellen. Mit der Hilfe von ChatGPT umgesetzt
    public function formatBiography(): string
    {
        return nl2br($this->biography);
    }
}
