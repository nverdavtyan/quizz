<?php

namespace App\Form;
use App\Entity\Reponse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('question', TextType::class, [
            'constraints' => [
                new NotBlank(['message' => 'You must enter a question']),
                new Length([
                    'min' => 5,
                    'max' => 50,
                    'minMessage' => 'You must enter a longer question',
                    'maxMessage' => 'You must enter a shorter question',
                ]),
            ],
        ])
        ->add('reponses', CollectionType::class, [
            'entry_type' => ReponseType::class,
            'label' => 'reponses',
            'allow_add' => true, //Allow more than 10 questions 
        ]);
    } 

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reponse::class,
        ]);
    }
}
