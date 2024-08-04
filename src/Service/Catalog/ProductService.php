<?php

namespace App\Service\Catalog;

interface ProductService
{
    public function add(string $name, int $price): Product;
    
    public function update(string $id, string $name, int $price): Product;

    public function remove(string $id): void;
}