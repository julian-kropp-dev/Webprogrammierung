<?php

namespace model\user;

require_once __DIR__ . "/../../exceptions/UserNotFoundException.php";

/*
 * ! Code wurde von der hochgeladenen Musterlösung genommen und angepasst !
 */

use IncorrectCredentialsException;
use UnknownEmailException;
use UserAlreadyExistsException;
use UserNotFoundException;
use InternalErrorException;

interface UserManagerInterface
{
    /**
     * @throws InternalErrorException
     */
    public function __construct();

    /**
     * Adds a new user.
     *
     * @param string $username The username of the new user.
     * @param string $email The email address of the new user.
     * @param string $password The password of the new user.
     * @param string $biography The biography of the new user (optional).
     * @param string $profilePhoto The filename of the new user's profile photo.
     * @throws UserAlreadyExistsException If the email address or username is already registered.
     * @throws InternalErrorException
     */
    public function createUser(string $username, string $email, string $password, string $biography, string $profilePhoto, string $registrationToken);

    /**
     * Retrieves a user by their email address.
     *
     * @param string $email The email address of the user.
     * @return User|null The user object if found, or null if not found.
     * @throws InternalErrorException If a general server error occurs.
     * @throws UnknownEmailException If the email is not known.
     */
    public function getUserByEmail(string $email): ?User;

    /**
     * @param string $username
     * @return bool true if user exists, otherwise false
     * @throws InternalErrorException If a general server error occurs.
     */
    public function isUserNameExisting(string $username) : bool;

    /**
     * Retrieves a user by their username.
     *
     * @param string $username The username of the user.
     * @return User|null The user object if found, or null if not found.
     * @throws InternalErrorException If a general server error occurs.
     */
    public function getUserByUsername(string $username): ?User;

    /**
     * Retrieves a user by their ID.
     *
     * @param int $id The ID of the user.
     * @return User The user object if found, or null if not found.
     * @throws UserNotFoundException
     * @throws InternalErrorException If a general server error occurs.
     */
    public function getUserById(int $id): User;

    /**
     * Checks if a user exists by either email or username.
     *
     * @param int $id The ID of the user.
     * @return bool True if the user exists, false otherwise.
     * @throws InternalErrorException If a general server error occurs.
     */
    public function isUserExisting(int $id): bool;

    /**
     * Updates user data.
     *
     * @param User $user The updated user object.
     * @throws InternalErrorException If a general server error occurs.
     * @throws UserAlreadyExistsException If the user already exists.
     * @throws IncorrectCredentialsException If the credentials are invalid.
     * @throws UserNotFoundException If the user cannot be found.
     * @throws UnknownEmailException If the Email is unknown.
     */
    public function updateUser(User $user);

    /**
     * Deletes a user by their ID.
     *
     * @param int $userId The ID of the user to delete.
     * @throws InternalErrorException If a general server error occurs.
     * @throws UserNotFoundException If the user cannot be found.
     */
    public function deleteUserById(int $userId): void;

    /**
     * Checks if an email is already registered.
     *
     * @param string $email The email to check.
     * @return bool True if the email is registered, false otherwise.
     * @throws InternalErrorException If a general server error occurs.
     */
    public function isEmailRegistered(string $email): bool;

    /**
     * Verifies if the user's credentials are valid.
     *
     * @param string $email The email address of the user.
     * @param string $password The password of the user.
     * @return bool True if the credentials are valid, false otherwise.
     * @throws InternalErrorException If a general server error occurs.
     * @throws IncorrectCredentialsException If the credentials are incorrect.
     * @throws UnknownEmailException If the Email is unknown.
     */
    public function isValidCredentials(string $email, string $password): bool;

    /**
     * Returns all users.
     *
     * @return array An array of user objects.
     * @throws InternalErrorException If a general server error occurs.
     */
    public function getAllUsers(): array;

    /**
     * Stores a registration token for an email address.
     *
     * @param string $email The email address of the user.
     * @param string $username The username of the new user.
     * @param string $password The password of the new user.
     * @param string $biography The biography of the new user (optional).
     * @param string $profilePhoto The filename of the new user's profile photo.
     * @param string $token The registration token.
     * @throws InternalErrorException If a general server error occurs.
     */
    public function storeRegistrationToken(string $email, string $username, string $password, string $token): void;

    /**
     * Retrieves the email address using a registration token.
     *
     * @param string $token The registration token.
     * @return array|null The registration data (email, username, password, biography, profile photo) if found, or null if not found.
     * @throws InternalErrorException If a general server error occurs.
     */
    public function getRegistrationDataByToken(string $token): ?array;

    /**
     * Deletes the token from the database.
     * @param string $email The email to delete all registration tokens from
     * @throws InternalErrorException If a general server error occurs.
     */
    public function deleteRegistrationTokens(string $email);
}
