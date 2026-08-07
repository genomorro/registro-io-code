<?php

namespace App\Form;

use App\Entity\Area;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class AreaAutocompleteField extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
	    'label' => 'Area',
            'class' => Area::class,
	    'choice_label' => function(Area $area)
	    {
		return sprintf(
		    '%s %s - %s',
		    $this->translator->trans('Building'),
		    $area->getBuilding(),
		    $area->getUnit());
	    },
	    'searchable_fields' => ['building', 'unit'],
	    'extra_options' => [],
	    'tom_select_options' => [
		'placeholder' => $this->translator->trans('Choose a Area'),
		'plugins' => [
		    'remove_button'=> true,
		    'clear_button' => false,
		],
	    ],	    
        ]);

	$resolver->setDefault('required', static function (Options $options) {
	    return $options['extra_options']['required'] ?? true;
	});
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
