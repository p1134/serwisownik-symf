<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password', PasswordType::class)
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                // 'mapped' => false,
                'invalid_message' => 'Hasła nie są identyczne',
                'attr' => [
                    'autocomplete' => 'new-password'
                ],
                'first_options' => [
                    'label' => 'Nowe hasło',
                    // 'mapped' => false
                ],
                'second_options' => [
                    'label' => 'Powtórz hasło',
                    // 'mapped' => false,
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Uzupełnij hasło',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Hasło powinno składać się z {{ limit }} znaków',
                        'max' => 4096,
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
