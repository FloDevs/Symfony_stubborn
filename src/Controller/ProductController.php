<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/products')]
final class ProductController extends AbstractController
{
    #[Route('/', name: 'app_products', methods: ['GET'])]
    public function list(Request $request, ProductRepository $productRepository): Response
    {
        $priceRange = $request->query->get('priceRange');

        if ($priceRange) {
            [$min, $max] = explode('-', $priceRange);
            $products = $productRepository->findByPriceRange((float)$min, (float)$max);
        } else {
            $products = $productRepository->findAll();
        }

        return $this->render('product/list.html.twig', [
            'products' => $products,
            'currentPriceRange' => $priceRange,
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
            'stock' => $product->getStock(),  
        ]);
    }

    #[Route('/{id}/add-to-cart', name: 'cart_add', methods: ['POST'])]
    public function addToCart(
        int $id, 
        Request $request, 
        CartService $cartService, 
        ProductRepository $productRepository
    ): Response 
    {
        $product = $productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé.');
        }

        $size = $request->request->get('size');
        $quantity = (int)$request->request->get('quantity', 1);

        // Vérification et ajout au panier
        $isAdded = $cartService->addProduct($id, $size, $productRepository, $quantity);

        if (!$isAdded) {
            $this->addFlash('error', 'Stock insuffisant pour la taille choisie.');
            return $this->redirectToRoute('app_product_show', ['id' => $id]);
        }

        $this->addFlash('success', 'Produit ajouté au panier.');
        return $this->redirectToRoute('app_product_show', ['id' => $id]);
    }

}

