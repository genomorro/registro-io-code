<?php

namespace App\Form;

use App\Entity\Patient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class PatientAutocompleteField extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'Patient',
            'class' => Patient::class,
            'placeholder' => 'Search by name or by file',
            'choice_label' => function(Patient $patient)
	    {
		return sprintf(
		    '(%s) %s',
		    $patient->getFile(),
		    $patient->getName());
	    },
            'searchable_fields' => ['file', 'name'],
            'extra_options' => [],
        ]);

        $resolver->setDefault('multiple', static function (Options $options) {
            return $options['extra_options']['multiple'] ?? false;
        });

        $resolver->setDefault('required', static function (Options $options) {
            return $options['extra_options']['required'] ?? true;
        });
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
