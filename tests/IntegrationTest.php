<?php

namespace App\Tests;

use App\Entity\Area;
use App\Form\AreaAutocompleteField;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class IntegrationTest extends KernelTestCase
{
    public function testTranslations(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $translator = $container->get(TranslatorInterface::class);
        $this->assertNotNull($translator);

        // Test English
        $buildingEn = $translator->trans('Building', [], 'messages', 'en');
        $this->assertEquals('Building', $buildingEn);

        // Test Spanish
        $buildingEs = $translator->trans('Building', [], 'messages', 'es');
        $this->assertEquals('Edificio', $buildingEs);

        $areaEn = $translator->trans('Area', [], 'messages', 'en');
        $this->assertEquals('Area', $areaEn);

        $areaEs = $translator->trans('Area', [], 'messages', 'es');
        $this->assertEquals('Área', $areaEs);
    }

    public function testAreaAutocompleteChoiceLabel(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $field = $container->get(AreaAutocompleteField::class);
        $this->assertNotNull($field);

        $resolver = new OptionsResolver();
        $field->configureOptions($resolver);
        $options = $resolver->resolve();

        $area = new Area();
        $area->setBuilding('3');
        $area->setUnit('ICU');

        $choiceLabel = $options['choice_label'];
        $this->assertIsCallable($choiceLabel);

        // Get translator expected translation of 'Building' for default locale
        $translator = $container->get(TranslatorInterface::class);
        $expectedPrefix = $translator->trans('Building');

        // By default, locale is 'en' or configured locale
        $label = $choiceLabel($area);
        $this->assertStringContainsString('3', $label);
        $this->assertStringContainsString('ICU', $label);
        $this->assertStringStartsWith($expectedPrefix, $label);
    }
}
