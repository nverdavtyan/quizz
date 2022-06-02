<?php

namespace App\Form;
use App\Form\QuestionType;
use App\Entity\Categorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuizType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('questions', CollectionType::class, [
                'entry_type' => QuestionType::class,
                'allow_add' => true, //Allow more than 10 questions  
                'by_reference' => false, //le parametre sauveur ? id_null cant flush    
                'label' => 'Questions List',
                // 'label_format' => 'form.questions.%name%',
                'entry_options' => [
                    // 'questions' => $options['quiz']->getQuestions(),
                ],
            ]);
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Categorie::class,
        ]);
    }
}
