<?php

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
