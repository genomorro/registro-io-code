<?php

namespace App\Tests\Controller;

use App\Entity\Area;
use App\Entity\Scheduled;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ScheduledImportControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::$kernel->getContainer()->get('doctrine')->getManager();

        // Ensure database schema is ready
        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        // Seed an Area
        $area = new Area();
        $area->setBuilding('Edificio 3');
        $area->setUnit('Unidad 33');
        $this->entityManager->persist($area);

        // Seed Admin User
        $user = new User();
        $user->setName('Admin User');
        $user->setUsername('admin');
        $user->setPassword('password');
        $user->setActive(true);
        $user->setRoles(['ROLE_ADMIN']);
        $this->entityManager->persist($user);

        // Seed Regular User
        $normalUser = new User();
        $normalUser->setName('Regular User');
        $normalUser->setUsername('user');
        $normalUser->setPassword('password');
        $normalUser->setActive(true);
        $normalUser->setRoles(['ROLE_USER']);
        $this->entityManager->persist($normalUser);

        $this->entityManager->flush();
    }

    public function testImportAccessDeniedForRegularUser(): void
    {
        $userRepository = static::$kernel->getContainer()->get('doctrine')->getRepository(User::class);
        $user = $userRepository->findOneBy(['username' => 'user']);

        $this->client->loginUser($user);
        $this->client->request('GET', '/es/scheduled/import');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testImportCsvFileDeduplicationAndConfirm(): void
    {
        $userRepository = static::$kernel->getContainer()->get('doctrine')->getRepository(User::class);
        $user = $userRepository->findOneBy(['username' => 'admin']);
        $this->client->loginUser($user);

        $area = static::$kernel->getContainer()->get('doctrine')->getRepository(Area::class)->findOneBy([]);
        $areaId = $area->getId();

        // File with duplicate labels (300234024 twice)
        $csvContent = "area_id,label,name,institution,subject,begin_at,end_at\n" .
            "{$areaId},300234024,\"Edgar Uriel Domínguez Espinoza\",UNAM,Trabajo,2026-09-10,2026-09-10\n" .
            "{$areaId},300234024,\"Edgar Uriel Duplicate\",UNAM,Trabajo,2026-09-10,2026-09-10\n";

        $tempFilePath = tempnam(sys_get_temp_dir(), 'test_import_') . '.csv';
        file_put_contents($tempFilePath, $csvContent);

        $uploadedFile = new UploadedFile(
            $tempFilePath,
            'test_import.csv',
            'text/csv',
            null,
            true
        );

        $this->client->request('GET', '/es/scheduled/import');
        $this->assertResponseIsSuccessful();

        $this->client->request('POST', '/es/scheduled/import', [], ['file' => $uploadedFile]);
        $this->assertResponseRedirects('/es/scheduled/import');

        $crawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Vista previa de datos a importar', $crawler->text());

        // Confirm import
        $this->client->request('POST', '/es/scheduled/import/confirm', ['update_existing' => '1']);
        $this->assertResponseRedirects('/es/scheduled');

        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $scheduledRepository = static::$kernel->getContainer()->get('doctrine')->getRepository(Scheduled::class);
        $all = $scheduledRepository->findBy(['label' => '300234024']);

        // Should only be imported ONCE (deduplicated)
        $this->assertCount(1, $all);
        $this->assertEquals('Edgar Uriel Domínguez Espinoza', $all[0]->getName());

        unlink($tempFilePath);
    }

    public function testExistingRecordPromptAndUpdateOption(): void
    {
        $userRepository = static::$kernel->getContainer()->get('doctrine')->getRepository(User::class);
        $user = $userRepository->findOneBy(['username' => 'admin']);
        $this->client->loginUser($user);

        $area = static::$kernel->getContainer()->get('doctrine')->getRepository(Area::class)->findOneBy([]);

        // Pre-existing scheduled record
        $existing = new Scheduled();
        $existing->setArea($area);
        $existing->setLabel('300234024');
        $existing->setName('Old Name');
        $existing->setInstitution('Old Inst');
        $existing->setSubject('Trabajo');
        $existing->setBeginAt(new \DateTimeImmutable('2026-09-10'));
        $existing->setEndAt(new \DateTimeImmutable('2026-09-10'));
        $this->entityManager->persist($existing);
        $this->entityManager->flush();

        $csvContent = "area_id,label,name,institution,subject,begin_at,end_at\n" .
            "{$area->getId()},300234024,\"New Name\",UNAM,Trabajo,2026-09-10,2026-09-10\n";

        $tempFilePath = tempnam(sys_get_temp_dir(), 'test_import_') . '.csv';
        file_put_contents($tempFilePath, $csvContent);

        $uploadedFile = new UploadedFile(
            $tempFilePath,
            'test_import.csv',
            'text/csv',
            null,
            true
        );

        $this->client->request('POST', '/es/scheduled/import', [], ['file' => $uploadedFile]);
        $crawler = $this->client->followRedirect();

        $this->assertStringContainsString('Registros existentes detectados', $crawler->text());

        // Confirm WITH update
        $this->client->request('POST', '/es/scheduled/import/confirm', ['update_existing' => '1']);
        $this->assertResponseRedirects('/es/scheduled');

        $scheduledRepository = static::$kernel->getContainer()->get('doctrine')->getRepository(Scheduled::class);
        $updated = $scheduledRepository->findOneBy(['label' => '300234024']);
        $this->assertEquals('New Name', $updated->getName());

        unlink($tempFilePath);
    }

    public function testImportXlsxFileAndValidationFailure(): void
    {
        $userRepository = static::$kernel->getContainer()->get('doctrine')->getRepository(User::class);
        $user = $userRepository->findOneBy(['username' => 'admin']);
        $this->client->loginUser($user);

        $area = static::$kernel->getContainer()->get('doctrine')->getRepository(Area::class)->findOneBy([]);
        $areaId = $area->getId();

        $rows = [
            ['area_id', 'label', 'name', 'institution', 'subject', 'begin_at', 'end_at'],
            [$areaId, '116250291', 'Karen Areli Nolasco Hernández', 'UREM', 'InvalidSubject', '2026-09-10', '2027-09-10']
        ];

        $tempFilePath = tempnam(sys_get_temp_dir(), 'test_import_') . '.xlsx';
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray($rows);
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($tempFilePath);

        $uploadedFile = new UploadedFile(
            $tempFilePath,
            'test_import.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $this->client->request('POST', '/es/scheduled/import', [], ['file' => $uploadedFile]);
        $this->assertResponseRedirects('/es/scheduled/import');

        $crawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $text = implode(' ', array_map(fn($el) => $el->textContent, iterator_to_array($crawler->filter('.alert-danger'))));
        $this->assertStringContainsString('subject', strtolower($text));

        unlink($tempFilePath);
    }
}
