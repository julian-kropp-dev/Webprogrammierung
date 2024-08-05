<?php
/*
 * ! The code itself was taken from the sample solutions and adjusted 
 *  ChatGPT helped with the docs
 */
namespace model\forum;

use ForumEntry;
use InternalErrorException;
use MissingEntryException;
use UnauthorizedAccessException;

interface ForumManagerInterface
{
	/**
	 * @throws InternalErrorException
	 */
	public function __construct();
	/**
	 * Inserts a new forum entry into the forum.
	 *
	 * @param int $creator_id ID of the user creating the forum entry.
	 * @param string $title Title of the forum entry.
	 * @param string $tags Tags of the forum entry.
	 * @param string $description Description of the forum entry.
	 * @param string $flickr_photo_id ID of the flickr photo the post creator selected
	 * @throws InternalErrorException
	 */
	public function newForumEntry(int $creator_id, string $title, string $tags, string $description, string $flickr_photo_id);

	/**
	 * Retrieves a forum entry by its ID.
	 *
	 * @param int $id ID of the forum entry.
	 * @return ForumEntry The requested forum entry.
	 * @throws MissingEntryException If the forum entry does not exist.
	 * @throws InternalErrorException
	 */
	public function getForumEntry(int $id): ForumEntry;

	/**
	 * Deletes a forum entry by its ID.
	 *
	 * @param int $delete_post_id ID of the forum entry to delete.
	 * @param int $user_id ID of the user who wants to delete the entry.
	 * @throws InternalErrorException
	 * @throws MissingEntryException
	 * @throws UnauthorizedAccessException
	 */
	public function deleteForumEntry(int $delete_post_id, int $user_id);


	/**
	 * Retrieves all existing forum entries.
	 *
	 * @param int $offset Starting Index of the post search
	 * @param int $limit Limit of the page count
	 * @return array An array of ForumEntry objects.
	 * @throws InternalErrorException
	 */
	public function getForumEntries(int $offset, int $limit): array;

	/**
	 * Creates a new forum comment.
	 *
	 * @param int $post_id ID of the forum entry the comment belongs to.
	 * @param int $user_id ID of the user creating the comment.
	 * @param string $content Content of the comment.
	 * @throws InternalErrorException
	 */
	public function newForumComment(int $post_id, int $user_id, string $content);


	/**
	 * Deletes a forum comment by its ID.
	 *
	 * @param int $post_id ID of the forum entry to delete.
	 * @param int $comment_id ID of the forum comment to delete.
	 * @param int $user_id User id who requested the deletion of the comment.
	 * @throws MissingEntryException
	 * @throws UnauthorizedAccessException
	 * @throws InternalErrorException
	 */
	public function deleteForumComment(int $post_id, int $comment_id, int $user_id): void;

	/**
	 * Updates a forum comment.
	 * 
	 * @param int $post_id
	 * @param int $comment_id
	 * @param int $user_id
	 * @param string $description
	 * @throws InternalErrorException
	 * @throws MissingEntryException
	 * @throws UnauthorizedAccessException
	 */
	public function updateForumComment(int $post_id, int $comment_id, int $user_id, string $description);

	/**
	 * Updates an existing forum entry.
	 *
	 * @param int $post_id ID of the forum entry to update.
	 * @param int $user_id User id who requested the update.
	 * @param string $title New title for the forum entry.
	 * @param string $tags New tags for the forum entry.
	 * @param string $description New description for the forum entry.
	 * @throws InternalErrorException
	 * @throws MissingEntryException
	 * @throws UnauthorizedAccessException
	 */
	public function updateForumEntry(int $post_id, int $user_id, string $title, string $tags, string $description);

	/**
	 * Searches for forum entries by the corresponding variable.
	 *
	 * @param string $title Title to search for.
	 * @param string $tags Tags to search for.
	 * @param string $sort_by The type to sort for.
	 * @param int $offset Starting Index of the post search
	 * @param int $limit Limit of the page count
	 * @return array An array of ForumEntry objects matching the search criteria and the count of the search.
	 * @throws InternalErrorException
	 */
	public function searchRequest(string $title, string $tags, string $sort_by, int $offset, int $limit): array;

	/**
	 * Returns the count of all existing entries in the forum table
	 * @return int Count of all existing entries
	 */
	public function getTotalForumEntriesCount(): int;
}