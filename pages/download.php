<?php
defined('INDEX_AUTH') OR die('Direct access not allowed!');

if (!defined('ADMIN_AREA')) {
    define('ADMIN_AREA', 'admin_area');
}

if (!defined('SB')) {
    require_once __DIR__ . '/../../../sysconfig.inc.php';
}

if (session_status() === PHP_SESSION_NONE) {
    require_once SB . 'admin/default/session.inc.php';
    require_once SB . 'admin/default/session_check.inc.php';
}

if (!utility::havePrivilege('reporting', 'r')) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

require_once __DIR__ . '/../lib/columns.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!defined('SLIMS_EXPORT_CAP')) {
    define('SLIMS_EXPORT_CAP', 20000);
}

$chosen = array_values(array_intersect(array_keys($REGISTRY), (array)($_GET['columns'] ?? array())));
if (count($chosen) < 1) {
    echo '<script>alert(' . json_encode(__('Pilih minimal satu kolom.')) . '); history.back();</script>';
    exit;
}
$where = buildWhere($_GET);
if (!empty($where['error'])) {
    echo '<script>alert(' . json_encode($where['error']) . '); history.back();</script>';
    exit;
}

$select = buildSelect($chosen);
if (empty($select)) {
    $select = 'b.biblio_id';
}

$headers = array();
foreach ($chosen as $token) {
    $headers[] = $REGISTRY[$token]['label'];
}
$whereSql = $where['sql'] ? ' WHERE ' . $where['sql'] : '';

$dbConfig = require SB . 'config' . DS . 'database.php';
$activeProfile = $dbConfig['default_profile'] ?? 'SLiMS';
$dbNode = $dbConfig['nodes'][$activeProfile] ?? reset($dbConfig['nodes']);
$dsn = 'mysql:host=' . $dbNode['host'] . ';port=' . $dbNode['port'] . ';dbname=' . $dbNode['database'] . ';charset=utf8';
$pdo = new PDO($dsn, $dbNode['username'], $dbNode['password'], array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
));
$pdo->query('SET NAMES utf8');
$pdo->query('SET SESSION group_concat_max_len=1048576');
$pdo->query("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");

$countStmt = $pdo->prepare('SELECT COUNT(DISTINCT b.biblio_id) FROM biblio b LEFT JOIN item i ON i.biblio_id = b.biblio_id' . $whereSql);
$countStmt->execute($where['params']);
$total = (int)$countStmt->fetchColumn();
$countStmt->closeCursor();
if ($total > SLIMS_EXPORT_CAP) {
    $msg = 'Ekspor dibatasi: ' . $total . ' judul melebihi batas ' . number_format(SLIMS_EXPORT_CAP, 0, ',', '.') . '. Persempit filter.';
    echo '<script>alert(' . json_encode($msg) . '); history.back();</script>';
    exit;
}

set_time_limit(0);
@ini_set('memory_limit', '512M');

$authorJoin = in_array('authors', $chosen, true)
    ? ' LEFT JOIN (SELECT ba.biblio_id, GROUP_CONCAT(DISTINCT ma.author_name ORDER BY ba.level ASC, ma.author_name ASC SEPARATOR \'; \') AS author_names FROM biblio_author ba INNER JOIN mst_author ma ON ma.author_id = ba.author_id GROUP BY ba.biblio_id) au ON au.biblio_id = b.biblio_id'
    : '';

$mainSql = 'SELECT ' . $select
    . ' FROM biblio b'
    . ' LEFT JOIN item i ON i.biblio_id = b.biblio_id'
    . ' LEFT JOIN loan l ON l.item_code = i.item_code AND l.is_return = 0'
    . ' LEFT JOIN mst_publisher mp ON mp.publisher_id = b.publisher_id'
    . ' LEFT JOIN mst_place mpl ON mpl.place_id = b.publish_place_id'
    . ' LEFT JOIN mst_language ml ON ml.language_id = b.language_id'
    . ' LEFT JOIN mst_gmd mg ON mg.gmd_id = b.gmd_id'
    . ' LEFT JOIN mst_location mloc ON mloc.location_id = i.location_id'
    . ' LEFT JOIN mst_coll_type mc ON mc.coll_type_id = i.coll_type_id'
    . ' LEFT JOIN mst_item_status mis ON mis.item_status_id = i.item_status_id'
    . $authorJoin
    . $whereSql
    . ' GROUP BY b.biblio_id'
    . ' ORDER BY b.biblio_id DESC';
$stmt = $pdo->prepare($mainSql);
$stmt->execute($where['params']);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Koleksi');

$numCols = count($headers);
$lastColLetter = Coordinate::stringFromColumnIndex($numCols);

// 1. Tulis Header dan Set Lebar Kolom Statis (Anti-Lag)
foreach ($headers as $c => $header) {
    $colLetter = Coordinate::stringFromColumnIndex($c + 1);
    $sheet->setCellValueExplicit($colLetter . '1', (string)$header, DataType::TYPE_STRING);
    
    $token = $chosen[$c];
    $colWidth = $REGISTRY[$token]['width'] ?? 20;
    $sheet->getColumnDimension($colLetter)->setWidth($colWidth);
}

// 2. Tulis Data Baris
$seqNumber = 1;
$rowNo = 2;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    foreach ($chosen as $c => $token) {
        $colLetter = Coordinate::stringFromColumnIndex($c + 1);
        $colInfo = $REGISTRY[$token];

        if ($token === 'row_no') {
            $sheet->setCellValueExplicit($colLetter . $rowNo, $seqNumber, DataType::TYPE_NUMERIC);
        } else {
            $alias = $colInfo['alias'];
            $v = $row[$alias] ?? '';
            if ($colInfo['type'] === 'numeric') {
                $sheet->setCellValueExplicit($colLetter . $rowNo, (int)$v, DataType::TYPE_NUMERIC);
            } else {
                $sheet->setCellValueExplicit($colLetter . $rowNo, (string)$v, DataType::TYPE_STRING);
            }
        }
    }
    $sheet->getRowDimension($rowNo)->setRowHeight(20);
    $rowNo++;
    $seqNumber++;
}

$lastRowNo = $rowNo - 1;

// 3. Styling Header Excel Profesional
$sheet->getRowDimension(1)->setRowHeight(28);
$headerStyle = array(
    'font' => array(
        'name' => 'Segoe UI',
        'bold' => true,
        'size' => 11,
        'color' => array('rgb' => 'FFFFFF'),
    ),
    'fill' => array(
        'fillType' => Fill::FILL_SOLID,
        'startColor' => array('rgb' => '1E3A5F'), // Navy Blue Elegan
    ),
    'alignment' => array(
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical'   => Alignment::VERTICAL_CENTER,
        'wrapText'   => true,
    ),
    'borders' => array(
        'allBorders' => array(
            'borderStyle' => Border::BORDER_THIN,
            'color' => array('rgb' => '2B4C7E'),
        ),
    ),
);
$sheet->getStyle('A1:' . $lastColLetter . '1')->applyFromArray($headerStyle);

// 4. Freeze Pane & AutoFilter
$sheet->freezePane('A2');
$sheet->setAutoFilter('A1:' . $lastColLetter . '1');

// 5. Styling Body Data (Borders, Font, Alignment, Numeric Format)
if ($lastRowNo >= 2) {
    // Default Font & Vertical Align untuk seluruh data
    $bodyStyle = array(
        'font' => array(
            'name' => 'Segoe UI',
            'size' => 10,
        ),
        'alignment' => array(
            'vertical' => Alignment::VERTICAL_CENTER,
        ),
        'borders' => array(
            'allBorders' => array(
                'borderStyle' => Border::BORDER_THIN,
                'color' => array('rgb' => 'E0E0E0'), // Grid abu-abu lembut
            ),
        ),
    );
    $sheet->getStyle('A2:' . $lastColLetter . $lastRowNo)->applyFromArray($bodyStyle);

    // Format Khusus Kolom Numerik & Row Number (Center Align + Thousand Separator)
    foreach ($chosen as $c => $token) {
        $colType = $REGISTRY[$token]['type'] ?? 'string';
        $colLetter = Coordinate::stringFromColumnIndex($c + 1);
        if ($colType === 'numeric' || $colType === 'row_number') {
            $sheet->getStyle($colLetter . '2:' . $colLetter . $lastRowNo)
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            if ($colType === 'numeric') {
                $sheet->getStyle($colLetter . '2:' . $colLetter . $lastRowNo)
                    ->getNumberFormat()->setFormatCode('#,##0');
            }
        }
    }
}

while (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="ekspor-koleksi-' . date('Ymd-His') . '.xlsx"');
header('Cache-Control: max-age=0');
header('Pragma: public');
$writer = new Xlsx($spreadsheet);
$writer->save(defined('SLIMS_EXPORT_TEST_OUTPUT') ? SLIMS_EXPORT_TEST_OUTPUT : 'php://output');
exit;
