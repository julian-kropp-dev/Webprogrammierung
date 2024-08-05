<?php

namespace model\posts;

use DateTime;
use InternalErrorException;
use model\user\User;
use model\util\Instances;

class Post
{
    private int $id;
    private User $user;
    private string $title;
    private string $image_src;
    private DateTime $date;
    private string $creation_timestamp;
    private string $reg_number;
    private string $manufacturer;
    private string $type;
    private string $airport;
    private string $camera;
    private string $lens;
    private string $iso;
    private string $aperture;
    private string $shutter;
    private int $likes;
    private array $comments;

    function __construct($post_data) {
        foreach ($post_data as $key => $value) {
            $this->$key = $value;
        }
    }

    public function getUser() :User
    {
        return $this->user;
    }

    public function getTitle() :string
    {
        return $this->title;
    }

    public function getImageSrc() :string
    {
        return $this->image_src;
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }

    public function getCreationTimestamp(): string
    {
        return $this->creation_timestamp;
    }

    public function getRegNumber() :string
    {
        return $this->reg_number;
    }

    public function getManufacturer() :string
    {
        return $this->manufacturer;
    }

    public function getType() :string
    {
        return $this->type;
    }

    public function getAirport() :string
    {
        return $this->airport;
    }

    public function getCamera() :string
    {
        return $this->camera;
    }

    public function getLens() :string
    {
        return $this->lens;
    }

    public function getIso() :string
    {
        return $this->iso;
    }

    public function getAperture() :string
    {
        return $this->aperture;
    }

    public function getShutter() :string
    {
        return $this->shutter;
    }

    public function getLikes() :int
    {
        return $this->likes;
    }

    public function &getComments() :array
    {
        return $this->comments;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setDate(DateTime $date): void
    {
        $this->date = $date;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function setRegNumber(string $reg_number): void
    {
        $this->reg_number = $reg_number;
    }

    public function setManufacturer(string $manufacturer): void
    {
        $this->manufacturer = $manufacturer;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function setAirport(string $airport): void
    {
        $this->airport = $airport;
    }

    public function setCamera(string $camera): void
    {
        $this->camera = $camera;
    }

    public function setLens(string $lens): void
    {
        $this->lens = $lens;
    }

    public function setIso(string $iso): void
    {
        $this->iso = $iso;
    }

    public function setAperture(string $aperture): void
    {
        $this->aperture = $aperture;
    }

    public function setShutter(string $shutter): void
    {
        $this->shutter = $shutter;
    }

    public function setImageSrc(string $image_src): void
    {
        $this->image_src = $image_src;
    }


    /**
     * @throws InternalErrorException
     */
    public function addComment(User $user, String $comment) : Comment {

        $comment = Instances::getPostManager()->addComment($this, $user, $comment);
        $this->comments[] = $comment;
        return $comment;

    }

    /**
     * @throws InternalErrorException
     */
    public function removeComment(int $id) : void
    {
        Instances::getPostManager()->deleteComment($this, $id);
        foreach ($this->comments as $key => $tComment){
            if ($tComment->getId() == $id){
                unset($this->comments[$key]);
            }
        }
    }



}

