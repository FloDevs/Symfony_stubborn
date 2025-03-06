<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use App\Repository\ProductRepository;

class CartService
{
    private $session;

    public function __construct(RequestStack $requestStack)
    {
        $this->session = $requestStack->getSession();
    }

    public function getCart(): array
    {
        return $this->session->get('cart', []);
    }

    public function getCartDetails(ProductRepository $productRepository): array
    {
    $cart = $this->getCart();
    $cartDetails = [];

    foreach ($cart as $key => $quantity) {
        [$productId, $size] = explode('-', $key);
        $product = $productRepository->find($productId);

        if ($product) {
            $cartDetails[] = [
                'product' => $product,
                'size' => $size,
                'quantity' => $quantity,
                'itemTotal' => $product->getPrice() * $quantity,
                'image' => $product->getImage(),
            ];
        }
    }

    return $cartDetails;
    }


    public function addProduct(int $productId, string $size, ProductRepository $productRepository, int $quantity = 1): bool
    {
    $cart = $this->getCart();
    $key = $productId . '-' . $size;

    $product = $productRepository->find($productId);
    if (!$product) {
        return false; 
    }

    $stockDisponible = $product->getStock();
    if (!is_array($stockDisponible) || !isset($stockDisponible[$size])) {
        return false; 
    }

    $currentQuantity = $cart[$key] ?? 0;

    if ($currentQuantity + $quantity > $stockDisponible[$size]) {
        return false; 
    }

    $cart[$key] = $currentQuantity + $quantity;
    $this->session->set('cart', $cart);

    return true; 
    }


    public function removeProduct(int $productId, string $size): void
    {
        $cart = $this->getCart();
        $key = $productId . '-' . $size;

        if (isset($cart[$key])) {
            unset($cart[$key]);
        }

        $this->session->set('cart', $cart);
    }

    public function clear(): void
    {
        $this->session->remove('cart');
    }

    public function getTotal(array $cartDetails): float
    {
        $total = 0;

        foreach ($cartDetails as $item) {
            $total += $item['itemTotal'];
        }

        return $total;
    }
}
