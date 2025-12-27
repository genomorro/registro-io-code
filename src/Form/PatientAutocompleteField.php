<?php

namespace App\Form;

use App\Entity\Patient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class PatientAutocompleteField extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'Patient',
            'class' => Patient::class,
            'choice_label' => function(Patient $patient)
	    {
		return sprintf(
		    '(%s) %s',
		    $patient->getFile(),
		    $patient->getName());
	    },
            'searchable_fields' => ['file', 'name'],
            'extra_options' => [],
	    'tom_select_options' => [
                'placeholder' => $this->translator->trans('Search by name or by file'),
		'plugins' => [
		    'remove_button' => true,
		    'clear_button' => false,
		],
	    ],
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
