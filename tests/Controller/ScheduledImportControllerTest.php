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

    public function testImportCsvFileSuccessfully(): void
    {
        $userRepository = static::$kernel->getContainer()->get('doctrine')->getRepository(User::class);
        $user = $userRepository->findOneBy(['username' => 'admin']);
        $this->client->loginUser($user);

        $area = static::$kernel->getContainer()->get('doctrine')->getRepository(Area::class)->findOneBy([]);
        $areaId = $area->getId();

        $csvContent = "area_id,label,name,institution,subject,begin_at,end_at\n" .
            "{$areaId},300234024,\"Edgar Uriel Domínguez Espinoza\",UNAM,Trabajo,2026-09-10,2026-09-10\n";

        $tempFilePath = tempnam(sys_get_temp_dir(), 'test_import_') . '.csv';
        file_put_contents($tempFilePath, $csvContent);

        $uploadedFile = new UploadedFile(
            $tempFilePath,
            'test_import.csv',
            'text/csv',
            null,
            true
        );

        $crawler = $this->client->request('GET', '/es/scheduled/import');
        $this->assertResponseIsSuccessful();

        $this->client->request('POST', '/es/scheduled/import', [], ['file' => $uploadedFile]);
        $this->assertResponseRedirects('/es/scheduled/import');

        $crawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Vista previa de datos a importar', $crawler->text());

        // Confirm import
        $this->client->request('POST', '/es/scheduled/import/confirm');
        $this->assertResponseRedirects('/es/scheduled');

        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $scheduledRepository = static::$kernel->getContainer()->get('doctrine')->getRepository(Scheduled::class);
        $scheduled = $scheduledRepository->findOneBy(['label' => '300234024']);

        $this->assertNotNull($scheduled);
        $this->assertEquals('Edgar Uriel Domínguez Espinoza', $scheduled->getName());
        $this->assertEquals('UNAM', $scheduled->getInstitution());
        $this->assertEquals('Trabajo', $scheduled->getSubject());
        $this->assertEquals($areaId, $scheduled->getArea()->getId());

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
