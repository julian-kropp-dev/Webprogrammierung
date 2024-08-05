<?php

use model\user\User;
use model\user\UserManagerInterface;

require_once __DIR__ . "/User.php";
require_once __DIR__ . "/UserManagerInterface.php";

class DummyUserManager implements UserManagerInterface
{
    private array $users = [];
    private array $registrationTokens = [];
    private int $userIdCounter = 4; // Beginnt mit 4, da wir 3 Benutzer in der Konstruktor-Methode haben

    public function __construct()
    {
        // Beispiel-Dummy-Daten
        $this->users[] = new User(1, 'julian@test.de', password_hash('1234', PASSWORD_DEFAULT), 'Julian', "Flugzeug-Liebhaber, Kamera-Enthusiast\n📌 Hamburg\n📸 Sony A7C, Canon 5DMII", "data/images/profile_pictures/1.jpeg");
        $this->users[] = new User(2, 'noa@test.de', password_hash('1234', PASSWORD_DEFAULT), 'Noa', "Motivierter Entwickler\n📌 Syke\n📸 iPhone 14-Pro", "data/images/profile_pictures/2.jpeg");
        $this->users[] = new User(3, 'anton@test.de', password_hash('1234', PASSWORD_DEFAULT), 'Anton', "Musik-Liebhaber, Film-Fan\n📌 München\n📸 iPhone 12, DJI Mavic Air", "data/images/profile_pictures/3.jpeg");
    }

    public function createUser(string $username, string $email, string $password, string $biography, string $profilePhoto, string $registrationToken): void
    {
        if ($this->isEmailRegistered($email)) {
            throw new UserAlreadyExistsException("Die E-Mail-Adresse ist bereits registriert.");
        }
        if ($this->getUserByUsername($username)) {
            throw new UserAlreadyExistsException("Der Benutzername ist bereits registriert.");
        }

        $this->users[] = new User($this->userIdCounter++, $email, password_hash($password, PASSWORD_DEFAULT), $username, $biography, $profilePhoto);
        $this->deleteRegistrationTokens($registrationToken);
    }

    public function getUserByEmail(string $email): ?User
    {
        foreach ($this->users as $user) {
            if ($user->getEmail() === $email) {
                return $user;
            }
        }
        throw new UnknownEmailException("Die E-Mail-Adresse ist nicht bekannt.");
    }

    public function getUserByUsername(string $username): ?User
    {
        foreach ($this->users as $user) {
            if ($user->getUsername() === $username) {
                return $user;
            }
        }
        return null;
    }

    public function isUserNameExisting(string $username): bool
    {
        return $this->getUserByUsername($username) != null;
    }


    public function getUserById(int $id): User
    {
        foreach ($this->users as $user) {
            if ($user->getId() === $id) {
                return $user;
            }
        }
        throw new UserNotFoundException();
    }

    public function updateUser(User $user): void
    {
        foreach ($this->users as &$storedUser) {
            if ($storedUser->getId() === $user->getId()) {
                if ($this->isEmailRegistered($user->getEmail()) && $storedUser->getEmail() !== $user->getEmail()) {
                    throw new UserAlreadyExistsException("Die E-Mail-Adresse ist bereits registriert.");
                }

                if ($this->getUserByUsername($user->getUsername()) && $storedUser->getUsername() !== $user->getUsername()) {
                    throw new UserAlreadyExistsException("Der Benutzername ist bereits registriert.");
                }

                if (!empty($user->getPassword())) {
                    if (!$this->isValidCredentials($user->getEmail(), $user->getPassword())) {
                        throw new IncorrectCredentialsException("Die Anmeldeinformationen sind ungültig.");
                    }
                }
                $storedUser = $user;
                return;
            }
        }
        throw new UserNotFoundException("Der Benutzer wurde nicht gefunden.");
    }

    public function deleteUserById(int $userId): void
    {
        foreach ($this->users as $key => $user) {
            if ($user->getId() === $userId) {
                unset($this->users[$key]);
                return;
            }
        }
        throw new UserNotFoundException("Der Benutzer wurde nicht gefunden.");
    }

    public function isUserExisting(int $id): bool
    {
        foreach ($this->users as $user) {
            if ($user->getId() === $id) {
                return true;
            }
        }
        return false;
    }

    public function isEmailRegistered(string $email): bool
    {
        foreach ($this->users as $user) {
            if ($user->getEmail() === $email) {
                return true;
            }
        }
        return false;
    }

    public function isValidCredentials(string $email, string $password): bool
    {
        foreach ($this->users as $user) {
            if ($user->getEmail() === $email && password_verify($password, $user->getPassword())) {
                return true;
            }
        }
        throw new IncorrectCredentialsException("Die Anmeldeinformationen sind ungültig.");
    }

    public function getAllUsers(): array
    {
        return $this->users;
    }

    public function storeRegistrationToken(string $email, string $username, string $password, string $token): void
    {
        $this->deleteRegistrationTokens($email);
        $this->registrationTokens[$token] = [
            'email' => $email,
            'username' => $username,
            'password' => $password,
        ];
    }

    public function getRegistrationDataByToken(string $token): ?array
    {
        return $this->registrationTokens[$token] ?? null;
    }

    public function getEmailByToken(string $token): ?string
    {
        return $this->registrationTokens[$token]['email'] ?? null;
    }

    public function deleteRegistrationTokens(string $email): void
    {
        foreach ($this->registrationTokens as $token => $tokenData) {
            if ($tokenData['email'] === $email) {
                unset($this->registrationTokens[$token]);
                 }
        }
    }
}
