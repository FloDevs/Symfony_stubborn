<?php

namespace App\Tests;

use App\Service\StripeService;
use PHPUnit\Framework\TestCase;
use Stripe\Checkout\Session;
use Stripe\StripeClient;

class StripeServiceTest extends TestCase
{
    private $stripeService;
    private string $fakeSecretKey = 'sk_test_1234567890abcdef'; // Clé fictive pour le test

    protected function setUp(): void
    {
        // Créer une instance du service Stripe avec une fausse clé API
        $this->stripeService = new StripeService($this->fakeSecretKey);
    }

    public function testCreateSession(): void
    {
        // 1️⃣ Mock du client Stripe
        $stripeMock = $this->createMock(StripeClient::class);

        // 2️⃣ Mock de la réponse de `Session::create()`
        $sessionMock = $this->createMock(Session::class);

        // 3️⃣ Mock de l'appel `checkout->sessions->create()`
        $stripeMock->checkout = new class {
            public $sessions;
            public function __construct() {
                $this->sessions = new class {
                    public function create($params) {
                        return new Session();
                    }
                };
            }
        };

        // 4️⃣ Simuler un panier
        $items = [
            [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => ['name' => 'Produit Test'],
                    'unit_amount' => 1000,
                ],
                'quantity' => 1,
            ],
        ];
        $successUrl = 'https://example.com/success';
        $cancelUrl = 'https://example.com/cancel';

        // 5️⃣ Exécuter `createSession()` et vérifier le résultat
        $session = $this->stripeService->createSession($items, $successUrl, $cancelUrl);
        $this->assertInstanceOf(Session::class, $session);
    }
}
