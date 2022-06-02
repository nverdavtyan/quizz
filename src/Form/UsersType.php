<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
class UsersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('email')
        ->add('username')
        ->add('password' ,PasswordType::class )
    ;
    $builder->add('status', ChoiceType::class, [
        'choices'  => [
            'Client' => 'ROLE_USER',
            'Admin' => 'ROLE_ADMIN'
        ]
    ]);
}

public function configureOptions(OptionsResolver $resolver)
{
    $resolver->setDefaults([
        'data_class' => User::class,
        'translation_domain' => 'forms'
    ]);
}
}
