<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Models\Item;
use App\Repository\ItemRepository;

class ItemController
{
    private ItemRepository $itemRepository;

    public function __construct()
    {
        $this->itemRepository = new ItemRepository();
    }

    /**
     * Displays the list of all shopping items.
     */
    public function index(): void
    {
        $items = $this->itemRepository->findAll();
        require __DIR__ . '/../Views/items/index.phtml';
    }

    /**
     * Handles the creation of a new item.
     */
    public function add(Request $request): void
    {
        $data = $request->getBody();
        $itemName = trim($data['name'] ?? '');

        if (!empty($itemName)) {
            $item = new Item();
            $item->name = $itemName;
            $this->itemRepository->save($item);
        }

        header('Location: /items');
        exit;
    }

    /**
     * Shows the form to edit an existing item.
     * Note the type hint (int) for the id from the URL.
     */
    public function edit(int $id): void
    {
        $item = $this->itemRepository->findById($id);

        if (!$item) {
            http_response_code(404);
            echo "404 - Item not found.";
            exit;
        }

        require __DIR__ . '/../Views/items/edit.phtml';
    }

    /**
     * Handles the form submission for updating an item.
     */
    public function update(Request $request, int $id): void
    {
        $item = $this->itemRepository->findById($id);

        if (!$item) {
            http_response_code(404);
            echo "404 - Item not found.";
            exit;
        }

        $data = $request->getBody();
        $newName = trim($data['name'] ?? '');

        if (!empty($newName)) {
            $item->name = $newName;
            $this->itemRepository->save($item);
        }

        header('Location: /items');
        exit;
    }

    /**
     * Handles toggling the checked state of an item.
     */
    public function toggle(Request $request): void
    {
        $data = $request->getBody();
        $id = (int)($data['id'] ?? 0);

        $item = $this->itemRepository->findById($id);

        if ($item) {
            $item->is_checked = !$item->is_checked;
            $this->itemRepository->save($item);
        }

        header('Location: /items');
        exit;
    }

    /**
     * Handles the deletion of an item.
     */
    public function delete(Request $request): void
    {
        $data = $request->getBody();
        $id = (int)($data['id'] ?? 0);

        if ($id > 0) {
            $this->itemRepository->delete($id);
        }

        header('Location: /items');
        exit;
    }
}
