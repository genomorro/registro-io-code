<?php

namespace App\Form;

use App\Entity\Employee;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class EmployeeAutocompleteField extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'Host',
            'class' => Employee::class,
            'choice_label' => 'name',
            'searchable_fields' => ['name'],
            'query_builder' => function (\Doctrine\ORM\EntityRepository $er) {
                return $er->createQueryBuilder('e')
                    ->andWhere('e.active = :active')
                    ->setParameter('active', true);
            },
            'extra_options' => [],
            'tom_select_options' => [
                'placeholder' => $this->translator->trans('Choose an Employee'),
                'plugins' => [
                    'remove_button' => true,
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
