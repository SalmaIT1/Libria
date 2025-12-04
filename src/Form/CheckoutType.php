<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CheckoutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('shippingAddress', TextType::class, [
                'label' => 'Shipping Address',
                'attr' => [
                    'placeholder' => '123 Main St, Apt 4B, City, State 12345',
                    'class' => 'form-control'
                ],
                'required' => true
            ])
            ->add('billingAddress', TextType::class, [
                'label' => 'Billing Address (leave same as shipping if identical)',
                'attr' => [
                    'placeholder' => '123 Main St, Apt 4B, City, State 12345',
                    'class' => 'form-control'
                ],
                'required' => false
            ])
            ->add('paymentMethod', ChoiceType::class, [
                'label' => 'Payment Method',
                'choices' => [
                    'Credit Card' => 'credit_card',
                    'Debit Card' => 'debit_card',
                    'PayPal' => 'paypal',
                    'Bank Transfer' => 'bank_transfer'
                ],
                'attr' => [
                    'class' => 'form-select'
                ],
                'required' => true
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Order Notes (optional)',
                'attr' => [
                    'placeholder' => 'Special instructions for your order...',
                    'rows' => 3,
                    'class' => 'form-control'
                ],
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
