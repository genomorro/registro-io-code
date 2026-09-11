<?php

namespace App\Tests\Service;

use App\Entity\Area;
use App\Entity\Scheduled;
use App\Repository\AreaRepository;
use App\Repository\ScheduledRepository;
use App\Service\ScheduledImporterService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class ScheduledImporterServiceTest extends TestCase
{
    private $areaRepository;
    private $scheduledRepository;
    private $entityManager;
    private ScheduledImporterService $service;

    protected function setUp(): void
    {
        $this->areaRepository = $this->createMock(AreaRepository::class);
        $this->scheduledRepository = $this->createMock(ScheduledRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->service = new ScheduledImporterService(
            $this->areaRepository,
            $this->scheduledRepository,
            $this->entityManager
        );
    }

    public function testAnalyzeFileInvalidExtension(): void
    {
        $res = $this->service->analyzeFile('/tmp/fake.pdf', 'fake.pdf');
        $this->assertNotEmpty($res['file_errors']);
        $this->assertStringContainsString('Extensión de archivo no permitida', $res['file_errors'][0]);
    }

    public function testAnalyzeFileMissingHeader(): void
    {
        $csvPath = sys_get_temp_dir() . '/test_missing_hdr.csv';
        file_put_contents($csvPath, "area_id,label,name\n1,100,John");

        $res = $this->service->analyzeFile($csvPath, 'test_missing_hdr.csv');
        unlink($csvPath);

        $this->assertNotEmpty($res['file_errors']);
        $this->assertStringContainsString('Faltan columnas requeridas', $res['file_errors'][0]);
    }

    public function testAnalyzeFileValidRowsAndDuplicates(): void
    {
        $csvPath = sys_get_temp_dir() . '/test_valid.csv';
        $csvContent = implode("\n", [
            'area_id,label,name,institution,subject,begin_at,end_at',
            '3,300234024,Edgar Uriel,UNAM,Cultura,2026-09-10,2026-09-10',
            '3,300234024,Edgar Uriel,UNAM,Cultura,2026-09-10,2026-09-10', // Duplicate in file
            '33,116250291,Karen Areli,UREM,Escuela,2026-09-10,2027-09-10',
            '999,999999,Invalid Area,Org,Trabajo,2026-09-10,2026-09-10', // Non-existing area
        ]);
        file_put_contents($csvPath, $csvContent);

        $area3 = new Area();
        $area3->setBuilding('Edificio A');

        $area33 = new Area();
        $area33->setBuilding('Edificio B');

        $this->areaRepository->expects($this->any())
            ->method('find')
            ->willReturnCallback(function ($id) use ($area3, $area33) {
                if ($id === 3) return $area3;
                if ($id === 33) return $area33;
                return null;
            });

        $this->scheduledRepository->expects($this->any())
            ->method('findOneBy')
            ->willReturn(null);

        $res = $this->service->analyzeFile($csvPath, 'test_valid.csv');
        unlink($csvPath);

        $this->assertEmpty($res['file_errors']);
        $this->assertEquals(4, $res['summary']['total']);
        $this->assertEquals(2, $res['summary']['new']);
        $this->assertEquals(1, $res['summary']['duplicate']);
        $this->assertEquals(1, $res['summary']['error']);

        // Check statuses
        $this->assertEquals('Nuevo', $res['rows'][0]['status']);
        $this->assertEquals('Duplicado', $res['rows'][1]['status']);
        $this->assertEquals('Nuevo', $res['rows'][2]['status']);
        $this->assertEquals('Error', $res['rows'][3]['status']);
    }

    public function testExecuteImportSkipAndCreate(): void
    {
        $area3 = new Area();

        $this->areaRepository->expects($this->any())
            ->method('find')
            ->with(3)
            ->willReturn($area3);

        $rows = [
            [
                'row_number' => 2,
                'area_id' => '3',
                'label' => '1001',
                'name' => 'Name 1',
                'institution' => 'Inst 1',
                'subject' => 'Trabajo',
                'begin_at' => '2026-01-01',
                'end_at' => '2026-01-02',
                'status' => 'Nuevo',
                'detail' => 'OK',
            ],
            [
                'row_number' => 3,
                'area_id' => '3',
                'label' => '1002',
                'name' => 'Name 2',
                'institution' => 'Inst 2',
                'subject' => 'Trabajo',
                'begin_at' => '2026-01-01',
                'end_at' => '2026-01-02',
                'status' => 'Existente',
                'detail' => 'Existente',
            ],
            [
                'row_number' => 4,
                'area_id' => '3',
                'label' => '1003',
                'name' => 'Name 3',
                'institution' => 'Inst 3',
                'subject' => 'Trabajo',
                'begin_at' => '2026-01-01',
                'end_at' => '2026-01-02',
                'status' => 'Error',
                'detail' => 'Error detail',
            ],
        ];

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $result = $this->service->executeImport($rows, 'skip');

        $this->assertEquals(1, $result['created']);
        $this->assertEquals(0, $result['updated']);
        $this->assertEquals(1, $result['omitted']);
        $this->assertEquals(1, $result['errors']);
        $this->assertCount(2, $result['unprocessed_rows']);
    }

    public function testGenerateErrorCsv(): void
    {
        $unprocessed = [
            [
                'row_number' => 3,
                'area_id' => '3',
                'label' => '1002',
                'name' => 'Name 2',
                'institution' => 'Inst 2',
                'subject' => 'Trabajo',
                'begin_at' => '2026-01-01',
                'end_at' => '2026-01-02',
                'status' => 'Existente',
                'detail' => 'Omitido por configuración.',
            ]
        ];

        $csv = $this->service->generateErrorCsv($unprocessed);
        $this->assertStringContainsString('area_id,label,name,institution,subject,begin_at,end_at,fila,estado,detalle', $csv);
        $this->assertStringContainsString('1002', $csv);
        $this->assertStringContainsString('Existente', $csv);
    }
}
