<?php
/**
 * Admin - Export Data to Excel
 */

require_once __DIR__ . '/../../config/init.php';

requireAdmin();

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Handle export
if (isset($_POST['export'])) {
    if (!verifyCsrfToken()) {
        setFlash('error', 'Invalid request.');
        redirect(SITE_URL . '/pages/admin/export.php');
    }

    $exportType = $_POST['export_type'] ?? 'registrations';

    // Check if PhpSpreadsheet is installed
    if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
        setFlash('error', 'PhpSpreadsheet is not installed. Run "composer install".');
        redirect(SITE_URL . '/pages/admin/export.php');
    }

    $pdo = getDBConnection();
    $spreadsheet = new Spreadsheet();

    if ($exportType === 'registrations' || $exportType === 'all') {
        // Registrations sheet
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Registrations');

        // Headers
        $headers = ['ID', 'Email', 'First Name', 'Last Name', 'Organization', 'Job Title',
                    'Phone', 'Country', 'Accommodation', 'Check-in', 'Check-out', 'Room Type',
                    'Dietary', 'Allergies', 'Status', 'Registered'];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        // Style headers
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '007bff']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A1:P1')->applyFromArray($headerStyle);

        // Data
        $stmt = $pdo->query("
            SELECT u.id, u.email, r.first_name, r.last_name, r.organization, r.job_title,
                   r.phone, r.country, r.needs_accommodation, r.check_in_date, r.check_out_date,
                   r.room_type, r.dietary_requirements, r.food_allergies, r.registration_status, r.created_at
            FROM users u
            LEFT JOIN registrations r ON u.id = r.user_id
            ORDER BY u.id
        ");

        $row = 2;
        while ($data = $stmt->fetch()) {
            $sheet->setCellValue('A' . $row, $data['id']);
            $sheet->setCellValue('B' . $row, $data['email']);
            $sheet->setCellValue('C' . $row, $data['first_name']);
            $sheet->setCellValue('D' . $row, $data['last_name']);
            $sheet->setCellValue('E' . $row, $data['organization']);
            $sheet->setCellValue('F' . $row, $data['job_title']);
            $sheet->setCellValue('G' . $row, $data['phone']);
            $sheet->setCellValue('H' . $row, $data['country']);
            $sheet->setCellValue('I' . $row, $data['needs_accommodation'] ? 'Yes' : 'No');
            $sheet->setCellValue('J' . $row, $data['check_in_date']);
            $sheet->setCellValue('K' . $row, $data['check_out_date']);
            $sheet->setCellValue('L' . $row, $data['room_type']);
            $sheet->setCellValue('M' . $row, $data['dietary_requirements']);
            $sheet->setCellValue('N' . $row, $data['food_allergies']);
            $sheet->setCellValue('O' . $row, $data['registration_status']);
            $sheet->setCellValue('P' . $row, $data['created_at']);
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'P') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    if ($exportType === 'abstracts' || $exportType === 'all') {
        // Abstracts sheet
        if ($exportType === 'all') {
            $sheet = $spreadsheet->createSheet();
        } else {
            $sheet = $spreadsheet->getActiveSheet();
        }
        $sheet->setTitle('Abstracts');

        // Headers
        $headers = ['ID', 'Email', 'Title', 'Authors', 'Abstract', 'Keywords',
                    'Presentation Type', 'Status', 'Has File', 'Submitted'];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        // Style headers
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '28a745']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);

        // Data
        $stmt = $pdo->query("
            SELECT a.*, u.email
            FROM abstracts a
            JOIN users u ON a.user_id = u.id
            ORDER BY a.id
        ");

        $row = 2;
        while ($data = $stmt->fetch()) {
            $sheet->setCellValue('A' . $row, $data['id']);
            $sheet->setCellValue('B' . $row, $data['email']);
            $sheet->setCellValue('C' . $row, $data['title']);
            $sheet->setCellValue('D' . $row, $data['authors']);
            $sheet->setCellValue('E' . $row, $data['abstract_text']);
            $sheet->setCellValue('F' . $row, $data['keywords']);
            $sheet->setCellValue('G' . $row, $data['presentation_type']);
            $sheet->setCellValue('H' . $row, $data['status']);
            $sheet->setCellValue('I' . $row, $data['file_path'] ? 'Yes' : 'No');
            $sheet->setCellValue('J' . $row, $data['submitted_at']);
            $row++;
        }

        // Auto-size columns (except abstract text)
        foreach (['A', 'B', 'C', 'D', 'F', 'G', 'H', 'I', 'J'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getColumnDimension('E')->setWidth(50);
    }

    // Set first sheet as active
    $spreadsheet->setActiveSheetIndex(0);

    // Download
    $filename = 'conference_export_' . date('Y-m-d_His') . '.xlsx';

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

$pageTitle = 'Export Data - ' . SITE_NAME;
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-file-earmark-excel me-2"></i>Export Data</h2>
            <p class="text-muted">Export conference data to Excel format.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-download me-2"></i>Export Options</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <?= csrfField() ?>
                        <input type="hidden" name="export" value="1">

                        <div class="mb-4">
                            <label class="form-label">Select Data to Export:</label>

                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="export_type"
                                       id="export_registrations" value="registrations" checked>
                                <label class="form-check-label" for="export_registrations">
                                    <strong>Registrations Only</strong>
                                    <br><small class="text-muted">User accounts and registration details</small>
                                </label>
                            </div>

                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="export_type"
                                       id="export_abstracts" value="abstracts">
                                <label class="form-check-label" for="export_abstracts">
                                    <strong>Abstracts Only</strong>
                                    <br><small class="text-muted">All submitted abstracts</small>
                                </label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="export_type"
                                       id="export_all" value="all">
                                <label class="form-check-label" for="export_all">
                                    <strong>All Data</strong>
                                    <br><small class="text-muted">Both registrations and abstracts (separate sheets)</small>
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-file-earmark-excel me-2"></i>Download Excel File
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Export Information</h5>
                </div>
                <div class="card-body">
                    <h6>Registrations Export Includes:</h6>
                    <ul>
                        <li>User email and ID</li>
                        <li>Personal information (name, organization, etc.)</li>
                        <li>Accommodation details</li>
                        <li>Dietary requirements</li>
                        <li>Registration status</li>
                    </ul>

                    <h6>Abstracts Export Includes:</h6>
                    <ul>
                        <li>Abstract title and authors</li>
                        <li>Full abstract text</li>
                        <li>Keywords and presentation type</li>
                        <li>Review status</li>
                        <li>Submission date</li>
                    </ul>

                    <div class="alert alert-info mb-0">
                        <i class="bi bi-lightbulb me-2"></i>
                        Files are exported in .xlsx format compatible with Excel, Google Sheets, and LibreOffice.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
