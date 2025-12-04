<?php

namespace App\Form;

use App\Entity\Auteur;
use App\Entity\Categorie;
use App\Entity\Editeur;
use App\Repository\AuteurRepository;
use App\Repository\CategorieRepository;
use App\Repository\EditeurRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookFilterType extends AbstractType
{
    public function __construct()
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('search', SearchType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Search by title, author, ISBN...',
                    'class' => 'form-control',
                ],
                'label' => false,
            ])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'designation',
                'required' => false,
                'placeholder' => 'All Categories',
                'attr' => ['class' => 'form-select'],
                'label' => 'Category',
            ])
            ->add('auteur', EntityType::class, [
                'class' => Auteur::class,
                'choice_label' => function (Auteur $auteur) {
                    return $auteur->getPrenom() . ' ' . $auteur->getNom();
                },
                'required' => false,
                'placeholder' => 'All Authors',
                'attr' => ['class' => 'form-select'],
                'label' => 'Author',
            ])
            ->add('editeur', EntityType::class, [
                'class' => Editeur::class,
                'choice_label' => 'nomEditeur',
                'required' => false,
                'placeholder' => 'All Publishers',
                'attr' => ['class' => 'form-select'],
                'label' => 'Publisher',
            ])
            ->add('minPrice', NumberType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Min',
                    'class' => 'form-control',
                    'step' => '0.01',
                    'min' => '0',
                ],
                'label' => 'Price Range',
            ])
            ->add('maxPrice', NumberType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Max',
                    'class' => 'form-control',
                    'step' => '0.01',
                    'min' => '0',
                ],
                'label' => false,
            ])
            ->add('minRating', NumberType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Min rating',
                    'class' => 'form-control',
                    'step' => '0.1',
                    'min' => '0',
                    'max' => '5',
                ],
                'label' => 'Minimum Rating',
            ])
            ->add('disponible', ChoiceType::class, [
                'choices' => [
                    'All Books' => '',
                    'Available Only' => '1',
                    'Unavailable' => '0',
                ],
                'required' => false,
                'attr' => ['class' => 'form-select'],
                'label' => 'Availability',
            ])
            ->add('sortBy', ChoiceType::class, [
                'choices' => [
                    'Title (A-Z)' => 'titre_asc',
                    'Title (Z-A)' => 'titre_desc',
                    'Price (Low to High)' => 'prix_asc',
                    'Price (High to Low)' => 'prix_desc',
                    'Pages (Low to High)' => 'pages_asc',
                    'Pages (High to Low)' => 'pages_desc',
                    'Date (Newest)' => 'date_desc',
                    'Date (Oldest)' => 'date_asc',
                    'Highest Rated' => 'rating_desc',
                    'Most Reviews' => 'reviews_desc',
                ],
                'required' => false,
                'placeholder' => 'Sort by...',
                'attr' => ['class' => 'form-select'],
                'label' => 'Sort',
            ])
            ->add('filter', SubmitType::class, [
                'label' => 'Apply Filters',
                'attr' => ['class' => 'btn btn-library'],
            ])
            ->add('reset', SubmitType::class, [
                'label' => 'Reset',
                'attr' => ['class' => 'btn btn-outline-secondary'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}

