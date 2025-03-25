<?php

namespace App\Form;

use App\Entity\User;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\BooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class,
            [
                'constraints' => [
                    new Length([
                        'min' => 3,
                        'max' => 20,
                        'minMessage' =>'Imię musi posiadać przynajmniej {{ limit }} znaki',
                        'maxMessage' => 'Imię nie moze być dłuzsze niz {{ limit }} znaków'
                    ])
                    ],
                    'required' => false,
            ])
            ->add('surname', TextType::class,
            [
                'constraints' => [
                    New Length([
                        'min' => 3,
                        'max' => 20,
                        'minMessage' =>'Nazwisko musi posiadać przynajmniej {{ limit }} znaki',
                        'maxMessage' => 'Nazwisko nie moze być dłuzsze niz {{ limit }} znaków'
                ])
                    ],
                'required' => false,
            ])
            ->add('phoneNumber', TelType::class, [
                'constraints' => [
                    new Length([
                        'min' => 9,
                        'max' => 9,
                        'exactMessage' => 'Podaj prawidłowy numer telefonu',
                    ]),
                    // new Regex([
                    //     'pattern' => '/^\d{9}$/', 
                    // ]),
            
                ],
            'required' => false,
            ])
            // ->add('sms', BooleanType::class, [
            //     'mapped' => false
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
