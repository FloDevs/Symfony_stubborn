<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin')]
final class AdminProductController extends AbstractController
{
    #[Route('/', name: 'admin_product_index', methods: ['GET', 'POST'])]
    public function index(Request $request, ProductRepository $productRepository, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Ajout d'un produit
        if ($request->isMethod('POST') && $request->request->get('action') === 'add') {
            $name = $request->request->get('name');
            $price = $request->request->get('price');
            $featured = $request->request->get('featured') ? true : false;
            $stock = $request->request->all('stock');

            if (!$name || !$price) {
                $this->addFlash('error', 'Veuillez remplir tous les champs obligatoires.');
            } else {
                $product = new Product();
                $product->setName($name);
                $product->setPrice((float) $price);
                $product->setFeatured($featured);
                $product->setStock($stock ?? ['XS' => 0, 'S' => 0, 'M' => 0, 'L' => 0, 'XL' => 0]);

                // Gestion de l'image
                $imageFile = $request->files->get('image');
                if ($imageFile) {
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                    try {
                        $imageFile->move($this->getParameter('uploads_directory'), $newFilename);
                    } catch (FileException) {
                        $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                    }

                    $product->setImage($newFilename);
                }

                $entityManager->persist($product);
                $entityManager->flush();
                $this->addFlash('success', 'Sweat-shirt ajouté avec succès.');
            }
        }

        // Modification d'un produit
        if ($request->isMethod('POST') && $request->request->get('action') === 'edit') {
            $productId = $request->request->get('id');
            $product = $productRepository->find($productId);

            if ($product) {
                $product->setPrice((float) $request->request->get('price'));
                $product->setFeatured($request->request->get('featured') ? true : false);
                $product->setStock($request->request->all('stock'));

                $imageFile = $request->files->get('image');
                if ($imageFile) {
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                    try {
                        $imageFile->move($this->getParameter('uploads_directory'), $newFilename);
                    } catch (FileException) {
                        $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                    }

                    $product->setImage($newFilename);
                }

                $entityManager->flush();
                $this->addFlash('success', 'Sweat-shirt mis à jour avec succès.');
            }
        }

        // Suppression d'un produit
        if ($request->isMethod('POST') && $request->request->get('action') === 'delete') {
            $productId = $request->request->get('id');
            $product = $productRepository->find($productId);

            if ($product) {
                $entityManager->remove($product);
                $entityManager->flush();
                $this->addFlash('success', 'Sweat-shirt supprimé avec succès.');
            }
        }

        return $this->render('admin/product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }
}
