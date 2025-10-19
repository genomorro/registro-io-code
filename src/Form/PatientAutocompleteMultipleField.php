<?php

namespace App\Form;

use App\Entity\Patient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class PatientAutocompleteMultipleField extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
	    'label' => 'Patient',
            'class' => Patient::class,
            'placeholder' => 'Search by name or by file',
            'choice_label' => 'name',
	    'multiple' => true,
	    'required'=> false,


            // choose which fields to use in the search
            // if not passed, *all* fields are used
            'searchable_fields' => ['name', 'file'],

            // 'security' => 'ROLE_SOMETHING',
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
