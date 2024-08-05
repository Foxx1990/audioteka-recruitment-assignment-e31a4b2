<?php

namespace App\Entity;

use App\Service\Catalog\Product;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
class Cart implements \App\Service\Cart\Cart
{
    public const CAPACITY = 3;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', nullable: false)]
    private UuidInterface $id;

    #[ORM\ManyToMany(targetEntity: 'Product')]
    #[ORM\JoinTable(name: 'cart_products')]
    private Collection $products;

    public function __construct(string $id)
    {
        $this->id = Uuid::fromString($id);
        $this->products = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id->toString();
    }

    public function getTotalPrice(): int
    {
        return array_reduce(
            $this->products->toArray(),
            static fn(int $total, Product $product): int => $total + $product->getPrice(),
            0
        );
    }

    public function isFull(): bool
    {
        return $this->products->count() >= self::CAPACITY;
    }

    public function getProducts(): iterable
    {
        return $this->products->getIterator();
    }

    public function hasProduct(Product $product): bool
    {
        foreach ($this->products as $cartProduct) {
            if ($cartProduct->getProduct() === $product) {
                return true;
            }
        }
        return false;
    }

    public function addProduct(Product $product): void
    {
        foreach ($this->products as $cartProduct) {
            if ($cartProduct->getProduct() === $product) {
                $cartProduct->increaseQuantity();
                return;
            }
        }

        $this->products->add(new CartProduct($this, $product));
    }

    public function removeProduct(Product $product): void
    {
        foreach ($this->products as $cartProduct) {
            if ($cartProduct->getProduct() === $product) {
                if ($cartProduct->getQuantity() > 1) {
                    $cartProduct->decreaseQuantity();
                } else {
                    $this->products->removeElement($cartProduct);
                }
                return;
            }
        }
    }
}
