<?php

namespace model\user;

use InternalErrorException;
use model\util\Instances;
use model\util\Util;
use PDO;
use PDOException;
use UnknownEmailException;
use UserAlreadyExistsException;
use UserNotFoundException;

require_once __DIR__ . "/User.php";
require_once __DIR__ . "/UserManagerInterface.php";
require_once __DIR__ . "/../../exceptions/UserAlreadyExistsException.php";
require_once __DIR__ . "/../../exceptions/UserNotFoundException.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class UserManagerPDOSQLite implements UserManagerInterface
{
    private PDO $connection;

    /**
     * @throws InternalErrorException
     */
    public function __construct()
    {
        try {
            $this->connection = Util::getDBConnection();
            $sql = $this->connection->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users';");

            if ($sql->fetch() === false) {
                // 'users' table does not exist, so create tables
                $this->initializeUserTables();
            }

            $sql = $this->connection->query("SELECT name FROM sqlite_master WHERE type='table' AND name='registration_tokens';");

            if ($sql->fetch() === false) {
                $this->initializeRegistrationTable();
            }
        } catch (PDOException) {
            throw new InternalErrorException();
        }

    }


    /**
     * Initializes the database.
     *
     */
    private function initializeUserTables(): void
    {
        $this->connection->exec("
        CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            username TEXT NOT NULL UNIQUE,
            biography TEXT,
            profile_photo TEXT
        );
    ");

        $this->connection->exec("
            INSERT INTO users (email, password, username, biography, profile_photo) VALUES
                ('julian@test.de', '" . password_hash('1234', PASSWORD_DEFAULT) . "', 'julian', 'Flugzeug-Liebhaber, Kamera-Enthusiast\n📌 Hamburg\n📸 Sony A7C, Canon 5DMII', 'data/images/profile_pictures/1.jpeg'),
                ('noa@test.de', '" . password_hash('1234', PASSWORD_DEFAULT) . "', 'noa', 'Motivierter Entwickler\n📌 Syke\n📸 iPhone 14-Pro', 'data/images/profile_pictures/2.jpeg'),
                ('anton@test.de', '" . password_hash('1234', PASSWORD_DEFAULT) . "', 'anton', 'Musik-Liebhaber, Film-Fan\n📌 München\n📸 iPhone 12, DJI Mavic Air', 'data/images/profile_pictures/3.jpeg');
        ");
    }

    private function initializeRegistrationTable(): void
    {
        $this->connection->exec("
        CREATE TABLE registration_tokens (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT NOT NULL,
            username TEXT NOT NULL,
            password TEXT NOT NULL,
            token TEXT NOT NULL,
            created_at DATETIME NOT NULL
        );
    ");
    }


    public function createUser(string $username, string $email, string $password, string $biography, string $profilePhoto, string $registrationToken): void
    {
        try {
            $this->connection->beginTransaction();

            if (empty($profilePhoto)) {
                $profilePhoto = 'data/images/profile_pictures/dummy.jpeg';
            }

            if ($this->isEmailRegistered($email)) {
                throw new UserAlreadyExistsException("Die E-Mail-Adresse ist bereits registriert.");
            }
            if ($this->isUserNameExisting($username)) {
                throw new UserAlreadyExistsException("Der Benutzername ist bereits registriert.");

            }

            $sql = "INSERT INTO users (username, email, password, biography, profile_photo) VALUES (:username, :email, :password, :biography, :profile_photo)";
            $command = $this->connection->prepare($sql);
            if (!$command) {
                throw new InternalErrorException("Failed to prepare SQL statement");
            }
            $command->execute([
                ':username' => $username,
                ':email' => $email,
                ':password' => $password,
                ':biography' => $biography,
                ':profile_photo' => $profilePhoto
            ]);

            $this->deleteRegistrationTokens($registrationToken);
            $this->connection->commit();
        } catch (PDOException $e) {
            try {
                $this->connection->rollBack();
            } catch (PDOException) {
            }
            throw new InternalErrorException("Internal server error");
        }
    }



    public function getUserByEmail(string $email): ?User
    {
        try {
            $db = $this->connection;
            $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
            $command = $db->prepare($sql);
            if (!$command) {
                throw new InternalErrorException("Failed to prepare SQL statement: " . implode(", ", $db->errorInfo()));
            }
            if (!$command->execute([':email' => $email])) {
                throw new InternalErrorException("Failed to execute SQL statement: " . implode(", ", $command->errorInfo()));
            }
            $result = $command->fetch(PDO::FETCH_ASSOC);
            if (!$result) {
                return null;
            }
            return new User($result['id'], $result['email'], $result['password'], $result['username'], $result['biography'], $result['profile_photo']);
        } catch (PDOException) {
            throw new InternalErrorException("Internal server error");
        }
    }

    public function isUserNameExisting(string $username) : bool
    {
        return $this->getUserByUsername($username) != null;
    }


    public function getUserByUsername(string $username): ?User
    {
        try {
            $db = $this->connection;
            $sql = "SELECT * FROM users WHERE username = :username";
            $command = $db->prepare($sql);
            if (!$command) {
                throw new InternalErrorException("Failed to prepare SQL statement");
            }
            if (!$command->execute([':username' => $username])) {
                throw new InternalErrorException("Failed to execute SQL statement");
            }
            $result = $command->fetch(PDO::FETCH_ASSOC);
            if (!$result) {
                return null;
            }
            return new User($result['id'], $result['email'], $result['password'], $result['username'], $result['biography'], $result['profile_photo']);
        } catch (PDOException) {
            throw new InternalErrorException("Internal server error");
        }
    }


    public function getUserById(int $id): User
    {
        try {
            $db = $this->connection;
            $sql = "SELECT * FROM users WHERE id = :id LIMIT 1";
            $command = $db->prepare($sql);
            if (!$command) {
                throw new InternalErrorException("Failed to prepare SQL statement");
            }
            if (!$command->execute([':id' => $id])) {
                throw new InternalErrorException("Failed to execute SQL statement");
            }
            $result = $command->fetch(PDO::FETCH_ASSOC);
            if (!$result) {
                throw new UserNotFoundException();
            }
            return new User($result['id'], $result['email'], $result['password'], $result['username'], $result['biography'], $result['profile_photo']);
        } catch (PDOException) {
            throw new InternalErrorException("Internal server error");
        }
    }


    public function isUserExisting(int $id): bool
    {
        try {
            $db = $this->connection;
            $sql = "SELECT COUNT(*) FROM users WHERE id = :id";
            $command = $db->prepare($sql);
            if (!$command) {
                throw new InternalErrorException("Failed to prepare SQL statement");
            }
            if (!$command->execute([':id' => $id])) {
                throw new InternalErrorException("Failed to execute SQL statement");
            }
            return (bool)$command->fetchColumn();
        } catch (PDOException) {
            throw new InternalErrorException("Internal server error");
        }
    }


    public function updateUser(User $user): void
    {
        try {
            $db = $this->connection;
            $sql = "UPDATE users SET email = :email, password = :password, username = :username, biography = :biography, profile_photo = :profile_photo WHERE id = :id";
            $command = $db->prepare($sql);
            if (!$command) {
                throw new InternalErrorException("Failed to prepare SQL statement");
            }
            if (!$command->execute([
                ':email' => $user->getEmail(),
                ':password' => $user->getPassword(),
                ':username' => $user->getUsername(),
                ':biography' => $user->getBiography(),
                ':profile_photo' => $user->getProfilePhoto(),
                ':id' => $user->getId()
            ])) {
                throw new InternalErrorException("Failed to execute SQL statement");
            }
        } catch (PDOException) {
            throw new InternalErrorException("Internal server error");
        }
    }


    public function deleteUserById(int $userId): void
    {
        $db = $this->connection;

        try {
            $db->beginTransaction();

            $sql = "DELETE FROM users WHERE id = :id";
            $command = $db->prepare($sql);
            if (!$command) {
                $db->rollBack();
                throw new InternalErrorException("Failed to prepare SQL statement");
            }
            if (!$command->execute([':id' => $userId])) {
                $db->rollBack();
                throw new InternalErrorException("Failed to execute SQL statement");
            }
            Instances::getPostManager()->deleteAllPostsFromUserById($userId);
            //In case forum tables do not exist
            Instances::getForumManager();
            $stmt = $this->connection->prepare("DELETE FROM forum WHERE creator_id = :id");
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            $stmt = $this->connection->prepare("DELETE FROM forum_comments WHERE user_id = :id");
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            $db->commit();
        } catch (PDOException) {
            try {
                $db->rollBack();
            } catch (PDOException) {
            }
            throw new InternalErrorException("Internal server error");
        }
    }

    public function isEmailRegistered(string $email): bool
    {
        try {
            $db = $this->connection;
            $sql = "SELECT COUNT(*) FROM users WHERE email = :email";
            $command = $db->prepare($sql);
            if (!$command) {
                throw new InternalErrorException("Failed to prepare SQL statement");
            }
            if (!$command->execute([':email' => $email])) {
                throw new InternalErrorException("Failed to execute SQL statement");
            }
            return (bool)$command->fetchColumn();
        } catch (PDOException|InternalErrorException) {
            throw new InternalErrorException("Internal server error");
        }
    }


    public function isValidCredentials(string $email, string $password): bool
    {
        try {
            $user = $this->getUserByEmail($email);
        } catch (UnknownEmailException) {
            throw new UnknownEmailException("Unknown Email");
        }
        return password_verify($password, $user->getPassword());
    }


    public function getAllUsers(): array
    {
        try {
            $db = $this->connection;
            $sql = "SELECT * FROM users";
            $command = $db->prepare($sql);
            if (!$command) {
                throw new InternalErrorException("Failed to prepare SQL statement");
            }
            if (!$command->execute()) {
                throw new InternalErrorException("Failed to execute SQL statement");
            }
            $result = $command->fetchAll(PDO::FETCH_ASSOC);
            $users = [];
            foreach ($result as $row) {
                $users[] = new User($row['id'], $row['email'], $row['password'], $row['username'], $row['biography'], $row['profile_photo']);
            }
            return $users;
        } catch (PDOException) {
            throw new InternalErrorException("Internal server error");
        }
    }

    public function storeRegistrationToken(string $email, string $username, string $password, string $token): void
    {
        try {
            $this->connection->beginTransaction();
            $this->deleteRegistrationTokens($email);

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO registration_tokens (email, username, password, token, created_at) VALUES (:email, :username, :password, :token, :created_at)";
            $command = $this->connection->prepare($sql);
            if (!$command) {
                throw new InternalErrorException("Failed to prepare SQL statement");
            }
            $result = $command->execute([
                ':email' => $email,
                ':username' => $username,
                ':password' => $hashedPassword,
                ':token' => $token,
                ':created_at' => date('Y-m-d H:i:s')
            ]);
            if (!$result) {
                throw new InternalErrorException("Failed to execute SQL statement");
            }
            $this->connection->commit();
        } catch (PDOException $e) {
            try {
                $this->connection->rollBack();
            } catch (PDOException $e) {
            }
            throw new InternalErrorException("Internal server error: " . $e->getMessage());
        }
    }



    public function getRegistrationDataByToken(string $token): ?array
    {
        try {
            $db = $this->connection;
            $sql = "SELECT email, username, password FROM registration_tokens WHERE token = :token LIMIT 1";
            $command = $db->prepare($sql);
            if (!$command) {
                throw new InternalErrorException("Failed to prepare SQL statement");
            }
            $command->execute([':token' => $token]);
            $result = $command->fetch(PDO::FETCH_ASSOC);
            if (!$result) {
                return null;
            }
            return $result;
        } catch (PDOException $e) {
            throw new InternalErrorException("Internal server error: " . $e->getMessage());
        }
    }

    public function deleteRegistrationTokens(string $email): void
    {
        try {
            $db = $this->connection;
            $sql = "DELETE FROM registration_tokens WHERE email = :email";
            $command = $db->prepare($sql);
            if (!$command) {
                throw new InternalErrorException("Failed to prepare SQL statement");
            }
            if (!$command->execute([':email' => $email])) {
                throw new InternalErrorException("Failed to execute SQL statement");
            }
        } catch (PDOException $e) {
            throw new InternalErrorException("Internal server error: " . $e->getMessage());
        }
    }


}