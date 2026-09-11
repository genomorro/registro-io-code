<?php

namespace App\Service;

use App\Entity\Scheduled;
use App\Repository\AreaRepository;
use App\Repository\ScheduledRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as SpreadsheetDate;

class ScheduledImporterService
{
    public const ALLOWED_SUBJECTS = [
        'Cultura',
        'Documentación',
        'Escuela',
        'Estudiante',
        'Expositor',
        'Medio de comunicación',
        'Proveedor',
        'Trabajo',
    ];

    public const EXPECTED_HEADERS = [
        'area_id',
        'label',
        'name',
        'institution',
        'subject',
        'begin_at',
        'end_at',
    ];

    public function __construct(
        private AreaRepository $areaRepository,
        private ScheduledRepository $scheduledRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Inspects and validates file structure and parses rows into analysis result.
     */
    public function analyzeFile(string $filepath, string $originalFilename): array
    {
        $extension = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));
        $fileErrors = [];

        if (!in_array($extension, ['csv', 'xlsx', 'xls'], true)) {
            return [
                'filename' => $originalFilename,
                'file_errors' => ['Extensión de archivo no permitida. Formatos aceptados: .csv, .xlsx, .xls'],
                'rows' => [],
                'summary' => [
                    'total' => 0,
                    'new' => 0,
                    'existing' => 0,
                    'duplicate' => 0,
                    'error' => 1,
                ],
            ];
        }

        // For CSV, validate encoding and delimiter
        if ($extension === 'csv') {
            $content = file_get_contents($filepath);
            if (!mb_check_encoding($content, 'UTF-8')) {
                $fileErrors[] = 'El encoding del archivo no es UTF-8 correcto.';
            }

            $firstLine = strtok($content, "\r\n");
            if ($firstLine !== false) {
                if (str_contains($firstLine, ';') && !str_contains($firstLine, ',')) {
                    $fileErrors[] = 'El delimitador del archivo no es correcto. Se requiere coma (,).';
                }
            }
        }

        try {
            $spreadsheet = IOFactory::load($filepath);
            $worksheet = $spreadsheet->getActiveSheet();
            $data = $worksheet->toArray(null, true, true, false);
        } catch (\Throwable $e) {
            return [
                'filename' => $originalFilename,
                'file_errors' => ['No se pudo leer el archivo: ' . $e->getMessage()],
                'rows' => [],
                'summary' => [
                    'total' => 0,
                    'new' => 0,
                    'existing' => 0,
                    'duplicate' => 0,
                    'error' => 1,
                ],
            ];
        }

        if (empty($data)) {
            return [
                'filename' => $originalFilename,
                'file_errors' => ['El archivo está vacío.'],
                'rows' => [],
                'summary' => [
                    'total' => 0,
                    'new' => 0,
                    'existing' => 0,
                    'duplicate' => 0,
                    'error' => 1,
                ],
            ];
        }

        // Header check
        $headerRow = array_map(fn($val) => trim((string) $val), $data[0]);

        // Check duplicate headers
        $headerCounts = array_count_values($headerRow);
        foreach ($headerCounts as $headerName => $count) {
            if ($count > 1 && $headerName !== '') {
                $fileErrors[] = sprintf('Cabecera duplicada encontrada: "%s".', $headerName);
            }
        }

        // Check missing / unknown / column count
        $missingColumns = array_diff(self::EXPECTED_HEADERS, $headerRow);
        if (!empty($missingColumns)) {
            $fileErrors[] = 'Faltan columnas requeridas en la cabecera: ' . implode(', ', $missingColumns);
        }

        $unknownColumns = array_diff($headerRow, self::EXPECTED_HEADERS);
        if (!empty($unknownColumns)) {
            $fileErrors[] = 'Columnas desconocidas en la cabecera: ' . implode(', ', $unknownColumns);
        }

        if (count($headerRow) !== count(self::EXPECTED_HEADERS)) {
            $fileErrors[] = sprintf('El número de columnas es incorrecto (%d encontradas, %d esperadas).', count($headerRow), count(self::EXPECTED_HEADERS));
        }

        if (!empty($fileErrors)) {
            return [
                'filename' => $originalFilename,
                'file_errors' => array_unique($fileErrors),
                'rows' => [],
                'summary' => [
                    'total' => 0,
                    'new' => 0,
                    'existing' => 0,
                    'duplicate' => 0,
                    'error' => 1,
                ],
            ];
        }

        // Parse data rows
        $rows = [];
        $seenLabels = [];
        $totalRows = 0;
        $countNew = 0;
        $countExisting = 0;
        $countDuplicate = 0;
        $countError = 0;

        // Map column positions based on header
        $headerMap = array_flip($headerRow);

        for ($i = 1; $i < count($data); $i++) {
            $rawRow = $data[$i];

            // Skip completely empty rows
            if (empty(array_filter($rawRow, fn($v) => $v !== null && trim((string)$v) !== ''))) {
                continue;
            }

            $fileRowIndex = $i + 1; // 1-indexed file row
            $totalRows++;

            $areaId = trim((string) ($rawRow[$headerMap['area_id']] ?? ''));
            $label = trim((string) ($rawRow[$headerMap['label']] ?? ''));
            $name = trim((string) ($rawRow[$headerMap['name']] ?? ''));
            $institution = trim((string) ($rawRow[$headerMap['institution']] ?? ''));
            $subject = trim((string) ($rawRow[$headerMap['subject']] ?? ''));
            $beginAtRaw = trim((string) ($rawRow[$headerMap['begin_at']] ?? ''));
            $endAtRaw = trim((string) ($rawRow[$headerMap['end_at']] ?? ''));

            $rowDetails = [];

            // Validation 1: area_id exists
            $area = null;
            if ($areaId === '' || !ctype_digit($areaId)) {
                $rowDetails[] = 'El campo area_id debe ser un entero válido.';
            } else {
                $area = $this->areaRepository->find((int) $areaId);
                if (!$area) {
                    $rowDetails[] = sprintf('El area_id %s no existe en la base de datos.', $areaId);
                }
            }

            // Validation 2: required fields
            if ($label === '') {
                $rowDetails[] = 'El campo label es obligatorio.';
            }
            if ($name === '') {
                $rowDetails[] = 'El campo name es obligatorio.';
            }
            if ($institution === '') {
                $rowDetails[] = 'El campo institution es obligatorio.';
            }

            // Validation 3: subject
            if (!in_array($subject, self::ALLOWED_SUBJECTS, true)) {
                $rowDetails[] = sprintf('El subject debe ser uno de: %s.', implode(', ', self::ALLOWED_SUBJECTS));
            }

            // Validation 4: dates
            $beginAtDate = $this->parseDate($beginAtRaw);
            $endAtDate = $this->parseDate($endAtRaw);

            if (!$beginAtDate) {
                $rowDetails[] = 'La fecha begin_at no tiene un formato válido (esperado YYYY-MM-DD).';
            }
            if (!$endAtDate) {
                $rowDetails[] = 'La fecha end_at no tiene un formato válido (esperado YYYY-MM-DD).';
            }
            if ($beginAtDate && $endAtDate && $endAtDate < $beginAtDate) {
                $rowDetails[] = 'La fecha end_at debe ser igual o mayor a begin_at.';
            }

            // Status determination
            $status = '';
            $detailMessage = '';

            if (!empty($rowDetails)) {
                $status = 'Error';
                $detailMessage = implode(' ', $rowDetails);
                $countError++;
            } elseif (isset($seenLabels[$label])) {
                $status = 'Duplicado';
                $detailMessage = sprintf('Etiqueta "%s" duplicada en el archivo (visto primero en la fila %d).', $label, $seenLabels[$label]);
                $countDuplicate++;
            } else {
                $seenLabels[$label] = $fileRowIndex;
                $existingInDb = $this->scheduledRepository->findOneBy(['label' => $label]);
                if ($existingInDb) {
                    $status = 'Existente';
                    $detailMessage = 'El registro con esta etiqueta ya existe en la base de datos.';
                    $countExisting++;
                } else {
                    $status = 'Nuevo';
                    $detailMessage = 'Registro válido para importar.';
                    $countNew++;
                }
            }

            $rows[] = [
                'row_number' => $fileRowIndex,
                'area_id' => $areaId,
                'label' => $label,
                'name' => $name,
                'institution' => $institution,
                'subject' => $subject,
                'begin_at' => $beginAtDate ? $beginAtDate->format('Y-m-d') : $beginAtRaw,
                'end_at' => $endAtDate ? $endAtDate->format('Y-m-d') : $endAtRaw,
                'status' => $status,
                'detail' => $detailMessage,
            ];
        }

        return [
            'filename' => $originalFilename,
            'file_errors' => [],
            'rows' => $rows,
            'summary' => [
                'total' => $totalRows,
                'new' => $countNew,
                'existing' => $countExisting,
                'duplicate' => $countDuplicate,
                'error' => $countError,
            ],
        ];
    }

    /**
     * Executes the import based on analyzed rows and the action for existing records ('skip' or 'update').
     */
    public function executeImport(array $rows, string $existingAction): array
    {
        $created = 0;
        $updated = 0;
        $omitted = 0;
        $errors = 0;

        $omittedOrErrorRows = [];

        foreach ($rows as $row) {
            $status = $row['status'];

            if ($status === 'Error' || $status === 'Duplicado') {
                $errors++;
                $omittedOrErrorRows[] = $row;
                continue;
            }

            if ($status === 'Existente') {
                if ($existingAction === 'skip') {
                    $omitted++;
                    $row['detail'] = 'Omitido por configuración (registro existente).';
                    $omittedOrErrorRows[] = $row;
                    continue;
                }

                // Update existing record
                $scheduled = $this->scheduledRepository->findOneBy(['label' => $row['label']]);
                if ($scheduled) {
                    $area = $this->areaRepository->find((int) $row['area_id']);
                    $scheduled->setName($row['name']);
                    $scheduled->setInstitution($row['institution']);
                    $scheduled->setSubject($row['subject']);
                    $scheduled->setBeginAt(new \DateTimeImmutable($row['begin_at']));
                    $scheduled->setEndAt(new \DateTimeImmutable($row['end_at']));
                    if ($area) {
                        $scheduled->setArea($area);
                    }
                    $updated++;
                } else {
                    $errors++;
                    $row['detail'] = 'Error al actualizar: no se encontró el registro en la BD.';
                    $omittedOrErrorRows[] = $row;
                }
                continue;
            }

            if ($status === 'Nuevo') {
                $area = $this->areaRepository->find((int) $row['area_id']);
                if ($area) {
                    $scheduled = new Scheduled();
                    $scheduled->setLabel($row['label']);
                    $scheduled->setName($row['name']);
                    $scheduled->setInstitution($row['institution']);
                    $scheduled->setSubject($row['subject']);
                    $scheduled->setBeginAt(new \DateTimeImmutable($row['begin_at']));
                    $scheduled->setEndAt(new \DateTimeImmutable($row['end_at']));
                    $scheduled->setArea($area);

                    $this->entityManager->persist($scheduled);
                    $created++;
                } else {
                    $errors++;
                    $row['detail'] = 'Error al crear: area_id no válido.';
                    $omittedOrErrorRows[] = $row;
                }
            }
        }

        $this->entityManager->flush();

        $importSuccess = ($created + $updated > 0) || ($total = count($rows) === 0);

        return [
            'success' => $importSuccess,
            'created' => $created,
            'updated' => $updated,
            'omitted' => $omitted,
            'errors' => $errors,
            'unprocessed_rows' => $omittedOrErrorRows,
        ];
    }

    /**
     * Generates CSV content for omitted and erroneous rows.
     */
    public function generateErrorCsv(array $unprocessedRows): string
    {
        $handle = fopen('php://temp', 'r+');

        // Output original headers plus fila, estado, detalle
        fputcsv($handle, ['area_id', 'label', 'name', 'institution', 'subject', 'begin_at', 'end_at', 'fila', 'estado', 'detalle']);

        foreach ($unprocessedRows as $row) {
            fputcsv($handle, [
                $row['area_id'],
                $row['label'],
                $row['name'],
                $row['institution'],
                $row['subject'],
                $row['begin_at'],
                $row['end_at'],
                $row['row_number'],
                $row['status'],
                $row['detail'],
            ]);
        }

        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);

        return $csvContent;
    }

    private function parseDate(string $val): ?\DateTimeImmutable
    {
        if ($val === '') {
            return null;
        }

        // Handle numeric Excel date representation
        if (is_numeric($val)) {
            try {
                $dt = SpreadsheetDate::excelToDateTimeObject($val);
                return \DateTimeImmutable::createFromMutable($dt);
            } catch (\Throwable $e) {
                return null;
            }
        }

        try {
            $dt = new \DateTimeImmutable($val);
            return $dt;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
