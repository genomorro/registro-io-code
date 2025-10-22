<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType as FormSearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class SearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FormSearchType::class, [
		'label' => 'File',
                'constraints' => [
                    new NotBlank(),
                    new Length(min: 6, max: 9),
                    new Callback([$this, 'validateFile']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }

    public function validateFile(?string $value, ExecutionContextInterface $context): void
    {
        if (empty($value)) {
            return;
        }

        if (str_starts_with(strtoupper($value), 'IAN')) {
            if (strlen($value) !== 9) {
                $context->buildViolation('If the file starts with "IAN", it must be 9 characters long.')
                    ->addViolation();
            }
        } elseif (!is_numeric($value)) {
            $context->buildViolation('The file must be numeric if it does not start with "IAN".')
                ->addViolation();
        }
    }
}
