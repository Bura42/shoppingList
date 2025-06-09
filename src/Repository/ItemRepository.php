<?php

declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;
use App\Models\Item;
use PDO;

/**
 * Handles data access logic for the Item model.
 */
class ItemRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Finds a single item by its primary key.
     *
     * @param int $id The ID of the item to find.
     * @return Item|null The found Item object, or null if not found.
     */
    public function findById(int $id): ?Item
    {
        $stmt = $this->pdo->prepare("SELECT * FROM items WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Set the fetch mode to our Item class
        $stmt->setFetchMode(PDO::FETCH_CLASS, Item::class);
        $item = $stmt->fetch();

        return $item ?: null;
    }


    /**
     * Fetches all items from the database.
     *
     * @return Item[] An array of Item objects.
     */
    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM items ORDER BY created_at DESC");
        $stmt->setFetchMode(PDO::FETCH_CLASS, Item::class);
        return $stmt->fetchAll();
    }

    /**
     * Saves an item to the database (creates if no ID, updates if ID exists).
     *
     * @param Item $item The item object to save.
     * @return bool True on success, false on failure.
     */
    public function save(Item $item): bool
    {
        if ($item->id) {
            $stmt = $this->pdo->prepare(
                "UPDATE items SET name = :name, is_checked = :is_checked WHERE id = :id"
            );
            $stmt->bindValue(':id', $item->id, PDO::PARAM_INT);
        } else {
            $stmt = $this->pdo->prepare(
                "INSERT INTO items (name, is_checked) VALUES (:name, :is_checked)"
            );
        }

        $stmt->bindValue(':name', $item->name, PDO::PARAM_STR);
        $stmt->bindValue(':is_checked', $item->is_checked, PDO::PARAM_BOOL);

        return $stmt->execute();
    }

    /**
     * Deletes an item from the database by its ID.
     *
     * @param int $id The ID of the item to delete.
     * @return bool True on success, false on failure.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM items WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
