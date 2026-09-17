<?php

namespace App\Form;

use App\Entity\Scheduled;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class ScheduledAutocompleteField extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
	    'label' => 'Scheduled',
            'class' => Scheduled::class,
            'choice_label' => function(Scheduled $scheduled)
	    {
		return sprintf(
		    '(%s) %s',
		    $scheduled->getLabel(),
		    $scheduled->getName());
	    },
	    'searchable_fields' => ['label', 'name'],
	    'query_builder' => function (\Doctrine\ORM\EntityRepository $er) {
		$today = new \DateTimeImmutable('today');
		return $er->createQueryBuilder('s')
		    ->andWhere('s.beginAt <= :today')
		    ->andWhere('s.endAt >= :today')
		    ->setParameter('today', $today);
	    },
	    'extra_options' => [],
	    'tom_select_options' => [
		'placeholder' => $this->translator->trans('Search by name or label'),
		'plugins' => [
		    'remove_button' => true,
		    'clear_button' => false,
		],
	    ],
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
