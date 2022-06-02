<?php

namespace App\Form;

use App\Entity\Reponse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
class ReponseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'You must enter a answer']),
                    new Length([
                        'min' => 5,
                        'max' => 50,
                        'minMessage' => 'You must enter a longer answer',
                        'maxMessage' => 'You must enter a shorter answer',
                        ])
                ],
            ])
            //putting CheckboxType makes all boxes required. so it breaks the purpose of the is_correct
            ->add('reponse_expected', CheckboxType::class)
            ->add('reponse_expected')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reponse::class,
        ]);
    }
}
