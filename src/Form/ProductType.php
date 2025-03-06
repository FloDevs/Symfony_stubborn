<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Nom du produit'])
            ->add('price', NumberType::class, ['label' => 'Prix (€)'])
            ->add('image', TextType::class, ['required' => false, 'label' => 'Image (URL)'])
            ->add('featured', CheckboxType::class, ['required' => false, 'label' => 'Mis en avant'])
            ->add('stock', CollectionType::class, [
                'entry_type' => NumberType::class,
                'entry_options' => ['label' => false],
                'allow_add' => false,
                'allow_delete' => false,
                'label' => 'Stock par taille (XS, S, M, L, XL)'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
