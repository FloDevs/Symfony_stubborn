<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Service\CartService;
use App\Service\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class CartController extends AbstractController
{
    #[Route('/cart', name: 'cart_show')]
    public function showCart(CartService $cartService, ProductRepository $productRepository): Response
    {
        $cartDetails = $cartService->getCartDetails($productRepository);
        $total = $cartService->getTotal($cartDetails);

        return $this->render('cart/show.html.twig', [
            'cart' => $cartDetails,
            'total' => $total,
        ]);
    }

    #[Route('/cart/remove/{productId}/{size}', name: 'cart_remove')]
    public function removeItem(int $productId, string $size, CartService $cartService): Response
    {
        $cartService->removeProduct($productId, $size);
        $this->addFlash('success', 'Article retiré du panier.');
        return $this->redirectToRoute('cart_show');
    }

    #[Route('/cart/checkout', name: 'cart_checkout')]
    public function checkout(CartService $cartService, ProductRepository $productRepository, StripeService $stripeService): Response
    {
        $cart = $cartService->getCart();
        $lineItems = [];

        foreach ($cart as $key => $quantity) {
            [$productId, $size] = explode('-', $key);
            $product = $productRepository->find($productId);

            if ($product) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => $product->getName() . ' (' . $size . ')',
                        ],
                        'unit_amount' => (int)($product->getPrice() * 100),
                    ],
                    'quantity' => $quantity,
                ];
            }
        }

        if (empty($lineItems)) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('cart_show');
        }
    

        $session = $stripeService->createSession(
            $lineItems,
            $this->generateUrl('cart_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            $this->generateUrl('cart_show', [], UrlGeneratorInterface::ABSOLUTE_URL)
        );

        return $this->redirect($session->url, 303);
    }

    // Confirmation de paiement
    #[Route('/cart/success', name: 'cart_success')]
    public function success(CartService $cartService): Response
    {
        $cartService->clear();
        $this->addFlash('success', 'Paiement réussi. Votre panier a été vidé.');
        return $this->redirectToRoute('cart_show');
    }

    // Vider le panier
    #[Route('/cart/clear', name: 'cart_clear')]
    public function clearCart(CartService $cartService): Response
    {
        $cartService->clear();
        $this->addFlash('success', 'Panier vidé avec succès.');
        return $this->redirectToRoute('cart_show');
    }
}
