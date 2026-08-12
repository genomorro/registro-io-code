<?php

namespace App\Tests\Form;

use App\Entity\Employee;
use App\Form\EmployeeAutocompleteField;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class EmployeeAutocompleteFieldTest extends TestCase
{
    public function testConfigureOptionsFiltersActiveEmployeesOnly(): void
    {
        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturn('Choose an Employee');

        $field = new EmployeeAutocompleteField($translator);

        $resolver = new OptionsResolver();
        $resolver->setDefined(['required', 'extra_options']);
        $field->configureOptions($resolver);

        $options = $resolver->resolve([
            'extra_options' => [],
        ]);

        $this->assertEquals(Employee::class, $options['class']);
        $this->assertArrayHasKey('query_builder', $options);

        // Verify the query builder filter
        $qb = $this->createMock(QueryBuilder::class);
        $repository = $this->createMock(EntityRepository::class);

        $repository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('e')
            ->willReturn($qb);

        $qb->expects($this->once())
            ->method('andWhere')
            ->with('e.active = :active')
            ->willReturnSelf();

        $qb->expects($this->once())
            ->method('setParameter')
            ->with('active', true)
            ->willReturnSelf();

        $queryBuilderClosure = $options['query_builder'];
        $result = $queryBuilderClosure($repository);

        $this->assertSame($qb, $result);
    }
}
