<?php

namespace App\Form;

use App\Entity\Area;
use App\Entity\Employee;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class EmployeeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number', null, [
		'label' => 'Work Number',
		'constraints' => [
		    new NotBlank(),
		]
	    ])
            ->add('name', null, [
		'label' => 'Name',
	    ])
            ->add('area', AreaAutocompleteField::class)
	    ->add('active', CheckboxType::class, [
		'label' => 'Is active?',
		'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Employee::class,
        ]);
    }
}
