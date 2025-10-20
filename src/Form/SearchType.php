<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SearchType as FormSearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('query', FormSearchType::class, [
                'label' => 'Search',
                'required' => false,
            ])
            ->add('criteria', ChoiceType::class, [
                'choices' => [
                    'Patient Name' => 'patient_name',
                    'Patient File' => 'patient_file',
                    'Patient Tag' => 'patient_tag',
                    'Visitor Name' => 'visitor_name',
                    'Visitor Tag' => 'visitor_tag',
                ],
                'label' => 'Criteria',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
