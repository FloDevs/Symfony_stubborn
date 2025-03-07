<?php

namespace App\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Service\CartService;
use App\Repository\ProductRepository;
use App\Entity\Product;

class CartServiceTest extends KernelTestCase
{
    private $cartService;
    private $session;
    private $productRepository;
    
    protected function setUp(): void
    {
        $this->session = $this->createMock(SessionInterface::class);
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getSession')->willReturn($this->session);
        
        $this->productRepository = $this->createMock(ProductRepository::class);
        $this->cartService = new CartService($requestStack);
    }

    public function testAddProduct(): void
    {
        $product = $this->createMock(Product::class);
        $product->method('getStock')->willReturn(['M' => 10]);
        $product->method('getPrice')->willReturn(20.0);
        
        $this->productRepository->method('find')->willReturn($product);

        $this->session->method('get')->willReturn([]);
        $this->session->expects($this->once())->method('set')->with('cart', ['1-M' => 1]);

        $result = $this->cartService->addProduct(1, 'M', $this->productRepository, 1);
        $this->assertTrue($result);
    }

    public function testGetCartDetails(): void
    {
        $cartData = ['1-M' => 2];

        $product = $this->createMock(Product::class);
        $product->method('getPrice')->willReturn(20.0);
        $product->method('getImage')->willReturn('image.jpg');

        $this->session->method('get')->willReturn($cartData);
        $this->productRepository->method('find')->willReturn($product);

        $cartDetails = $this->cartService->getCartDetails($this->productRepository);
        
        $this->assertCount(1, $cartDetails);
        $this->assertEquals(40, $cartDetails[0]['itemTotal']);
    }

    public function testRemoveProduct(): void
    {
        $cartData = ['1-M' => 2];

        $this->session->method('get')->willReturn($cartData);
        $this->session->expects($this->once())->method('set')->with('cart', []);

        $this->cartService->removeProduct(1, 'M');
    }

    public function testClear(): void
    {
        $this->session->expects($this->once())->method('remove')->with('cart');

        $this->cartService->clear();
    }

    public function testGetTotal(): void
    {
        $cartDetails = [
            ['itemTotal' => 30],
            ['itemTotal' => 50]
        ];

        $total = $this->cartService->getTotal($cartDetails);
        $this->assertEquals(80, $total);
    }
}
