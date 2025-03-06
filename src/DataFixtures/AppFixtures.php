<?php
namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Exemples de sweatshirts
        $productsData = [
            ['name' => 'Blackbelt', 'price' => 29.90, 'featured' => true],
            ['name' => 'BlueBelt', 'price' => 29.90, 'featured' => false],
            ['name' => 'Street', 'price' => 34.50, 'featured' => false],
            ['name' => 'Pokeball', 'price' => 45.00, 'featured' => true],
            ['name' => 'PinkLady', 'price' => 29.90, 'featured' => false],
            ['name' => 'Snow', 'price' => 32.00, 'featured' => false],
            ['name' => 'Greyback', 'price' => 28.50, 'featured' => false],
            ['name' => 'BlueCloud', 'price' => 45.00, 'featured' => false],
            ['name' => 'BornInUsa', 'price' => 59.90, 'featured' => true],
            ['name' => 'GreenSchool', 'price' => 42.20, 'featured' => false],
        ];

        // Pour chaque sweatshirt, on va créer 5 déclinaisons (XS, S, M, L, XL)
        $sizes = ['XS', 'S', 'M', 'L', 'XL'];
        foreach ($productsData as $pData) {
            foreach ($sizes as $size) {
                $product = new Product();
                $product->setName($pData['name']);
                $product->setPrice($pData['price']);
                $product->setFeatured($pData['featured']);
                $product->setSize($size);
                $product->setStock(2); // Au moins 2 exemplaires
                $manager->persist($product);
            }
        }

        $manager->flush();
    }
}
