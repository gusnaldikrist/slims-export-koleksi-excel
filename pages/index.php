<?php
defined('INDEX_AUTH') OR die('Direct access not allowed!');

if (session_status() === PHP_SESSION_NONE) {
    require_once SB . 'admin/default/session.inc.php';
    require_once SB . 'admin/default/session_check.inc.php';
}

require_once __DIR__ . '/../lib/columns.php';
require_once SIMBIO . 'simbio_GUI/form_maker/simbio_form_element.inc.php';

if (!utility::havePrivilege('reporting', 'r')) {
    http_response_code(403);
    echo '<div class="alert alert-danger m-3">' . __('You don\'t have enough privileges to access this area!') . '</div>';
    exit;
}

// Router untuk aksi download file Excel via admin plugin_container
if (isset($_GET['action']) && $_GET['action'] === 'download') {
    require __DIR__ . '/download.php';
    exit;
}

global $dbs;

// Opsi dropdown master (value = ID, label = nama, '' = ALL)
$loc_q = $dbs->query('SELECT location_id, location_name FROM mst_location ORDER BY location_name');
$loc_options = array(array('', __('SEMUA LOKASI')));
while ($loc_d = $loc_q->fetch_row()) {
    $loc_options[] = array((string)$loc_d[0], $loc_d[1]);
}
$coll_q = $dbs->query('SELECT coll_type_id, coll_type_name FROM mst_coll_type ORDER BY coll_type_name');
$coll_options = array(array('', __('SEMUA TIPE KOLEKSI')));
while ($coll_d = $coll_q->fetch_row()) {
    $coll_options[] = array((string)$coll_d[0], $coll_d[1]);
}
$status_q = $dbs->query('SELECT item_status_id, item_status_name FROM mst_item_status ORDER BY item_status_name');
$status_options = array(array('', __('SEMUA STATUS EKSEMPLAR')));
while ($status_d = $status_q->fetch_row()) {
    $status_options[] = array((string)$status_d[0], $status_d[1]);
}

// Pisahkan kolom untuk visual grouping yang rapi (Opsi 1: Satu Baris per Judul Koleksi)
$biblio_tokens = ['row_no', 'title', 'authors', 'publisher', 'publish_year', 'publish_place', 'isbn_issn', 'classification', 'call_number', 'edition', 'language', 'gmd'];
$item_tokens   = ['total_items', 'available_items', 'loaned_items', 'other_items', 'item_codes', 'locations', 'coll_types', 'item_statuses', 'inventory_codes'];
?>

<div class="menuBox">
    <div class="menuBoxInner">
        <div class="per_title">
            <h2><i class="fa fa-file-excel-o text-success mr-2"></i><?= __('Ekspor Koleksi & Eksemplar ke Excel') ?></h2>
        </div>
        <div class="infoBox">
            <?= __('Ekspor gabungan data bibliografi, rincian ketersediaan stok fisik (Tersedia, Dipinjam, Rusak/Khusus), dan ringkasan eksemplar ke spreadsheet Excel (.xlsx) profesional.') ?>
        </div>
    </div>
</div>

<div class="container-fluid mt-3 mb-5">
    <form method="get" action="<?= AWB ?>plugin_container.php" id="exportExcelForm">
        <input type="hidden" name="mod" value="<?= htmlspecialchars($_GET['mod'] ?? 'reporting', ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="id" value="<?= htmlspecialchars($_GET['id'] ?? md5(realpath(__FILE__)), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="action" value="download">
        
        <!-- CARD 1: PILIHAN KOLOM DATA -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-bottom">
                <div>
                    <h5 class="m-0 font-weight-bold text-dark">
                        <i class="fa fa-columns text-primary mr-2"></i><?= __('Pilihan Kolom yang Diekspor') ?>
                    </h5>
                    <small class="text-muted"><?= __('Centang kolom data yang ingin dimuat ke dalam sheet Excel.') ?></small>
                </div>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-primary" id="btnSelectAll">
                        <i class="fa fa-check-square-o mr-1"></i><?= __('Pilih Semua') ?>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="btnDeselectAll">
                        <i class="fa fa-square-o mr-1"></i><?= __('Hapus Semua') ?>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Kolom Bibliografi -->
                    <div class="col-lg-6 pr-lg-4 border-right">
                        <h6 class="text-uppercase text-muted font-weight-bold mb-3 small">
                            <i class="fa fa-book text-info mr-1"></i> <?= __('Data Bibliografi / Katalog') ?> (<?= count($biblio_tokens) ?>)
                        </h6>
                        <div class="column-grid">
                            <?php foreach ($biblio_tokens as $token): ?>
                                <?php if (isset($REGISTRY[$token])): ?>
                                <label class="column-item custom-control custom-checkbox">
                                    <input type="checkbox" name="columns[]" value="<?= $token ?>" class="custom-control-input col-chk" checked>
                                    <span class="custom-control-label"><?= $token === 'row_no' ? __('Nomor Urut (No.)') : $REGISTRY[$token]['label'] ?></span>
                                </label>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Kolom Eksemplar & Stok -->
                    <div class="col-lg-6 pl-lg-4 mt-3 mt-lg-0">
                        <h6 class="text-uppercase text-muted font-weight-bold mb-3 small">
                            <i class="fa fa-cubes text-success mr-1"></i> <?= __('Data Eksemplar & Ketersediaan Stok') ?> (<?= count($item_tokens) ?>)
                        </h6>
                        <div class="column-grid">
                            <?php foreach ($item_tokens as $token): ?>
                                <?php if (isset($REGISTRY[$token])): ?>
                                <label class="column-item custom-control custom-checkbox">
                                    <input type="checkbox" name="columns[]" value="<?= $token ?>" class="custom-control-input col-chk" checked>
                                    <span class="custom-control-label font-weight-500"><?= $REGISTRY[$token]['label'] ?></span>
                                </label>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD 2: FILTER & KRITERIA PENCARIAN -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="m-0 font-weight-bold text-dark">
                    <i class="fa fa-filter text-primary mr-2"></i><?= __('Filter & Pembatasan Data (Opsional)') ?>
                </h5>
                <small class="text-muted"><?= __('Biarkan kosong atau pilih SEMUA jika ingin mengekspor seluruh koleksi.') ?></small>
            </div>
            <div class="card-body">
                <!-- Filter Tanggal Input -->
                <div class="form-group row mb-4">
                    <label class="col-md-3 col-form-label font-weight-bold text-md-right pt-2">
                        <?= __('Rentang Tanggal Input') ?>
                    </label>
                    <div class="col-md-9">
                        <div class="form-row align-items-center">
                            <div class="col-sm-5 col-md-4">
                                <?= simbio_form_element::dateField('date_from', '', 'class="form-control" placeholder="Dari: YYYY-MM-DD"') ?>
                            </div>
                            <div class="col-auto text-muted my-1 text-center font-weight-bold">
                                <?= __('s/d') ?>
                            </div>
                            <div class="col-sm-5 col-md-4">
                                <?= simbio_form_element::dateField('date_to', '', 'class="form-control" placeholder="Sampai: YYYY-MM-DD"') ?>
                            </div>
                        </div>
                        <div class="custom-control custom-checkbox mt-2">
                            <input type="checkbox" class="custom-control-input" id="include_null_date" name="include_null_date" value="1">
                            <label class="custom-control-label text-muted small" for="include_null_date">
                                <?= __('Sertakan juga koleksi yang tidak memiliki tanggal input') ?>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Filter Master Dropdowns -->
                <div class="form-group row mb-3">
                    <label class="col-md-3 col-form-label font-weight-bold text-md-right pt-2">
                        <?= __('Lokasi Rak') ?>
                    </label>
                    <div class="col-md-7 col-lg-5">
                        <?= simbio_form_element::selectList('location', $loc_options, '', 'class="form-control"') ?>
                    </div>
                </div>

                <div class="form-group row mb-3">
                    <label class="col-md-3 col-form-label font-weight-bold text-md-right pt-2">
                        <?= __('Tipe Koleksi') ?>
                    </label>
                    <div class="col-md-7 col-lg-5">
                        <?= simbio_form_element::selectList('coll_type', $coll_options, '', 'class="form-control"') ?>
                    </div>
                </div>

                <div class="form-group row mb-3">
                    <label class="col-md-3 col-form-label font-weight-bold text-md-right pt-2">
                        <?= __('Status Eksemplar') ?>
                    </label>
                    <div class="col-md-7 col-lg-5">
                        <?= simbio_form_element::selectList('item_status', $status_options, '', 'class="form-control"') ?>
                    </div>
                </div>

                <!-- Filter Prefix Klasifikasi -->
                <div class="form-group row mb-2">
                    <label class="col-md-3 col-form-label font-weight-bold text-md-right pt-2">
                        <?= __('Prefix Nomor Klasifikasi') ?>
                    </label>
                    <div class="col-md-6 col-lg-4">
                        <input type="text" name="class_prefix" class="form-control" placeholder="<?= __('Contoh: 005 atau 2X1') ?>" maxlength="50">
                        <small class="form-text text-muted"><?= __('Menyaring koleksi dengan nomor klasifikasi yang diawali teks ini.') ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- ACTION BAR -->
        <div class="card shadow-sm border-0 mb-4 bg-light">
            <div class="card-body py-3 d-flex flex-column flex-sm-row justify-content-between align-items-center">
                <div class="text-muted small mb-3 mb-sm-0">
                    <i class="fa fa-info-circle mr-1 text-primary"></i> <?= __('Maksimal ekspor dibatasi hingga 20.000 judul per unduhan.') ?>
                </div>
                <div>
                    <button type="submit" class="btn btn-success btn-lg px-4 font-weight-bold" id="btnDownload">
                        <i class="fa fa-file-excel-o mr-2"></i> <?= __('Unduh File Excel (.xlsx)') ?>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
.column-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
    gap: 0.6rem 1rem;
}
.column-item {
    margin-bottom: 0;
    padding-top: 0.25rem;
    padding-bottom: 0.25rem;
    cursor: pointer;
    user-select: none;
}
.column-item:hover .custom-control-label {
    color: #007bff;
}
.font-weight-500 {
    font-weight: 500;
}
</style>

<script>
(function() {
    var form = document.getElementById('exportExcelForm');
    var btnSelectAll = document.getElementById('btnSelectAll');
    var btnDeselectAll = document.getElementById('btnDeselectAll');
    var checkboxes = document.querySelectorAll('.col-chk');

    if (btnSelectAll) {
        btnSelectAll.addEventListener('click', function() {
            checkboxes.forEach(function(chk) { chk.checked = true; });
        });
    }

    if (btnDeselectAll) {
        btnDeselectAll.addEventListener('click', function() {
            checkboxes.forEach(function(chk) { chk.checked = false; });
        });
    }

    if (form) {
        form.addEventListener('submit', function(e) {
            var anyChecked = Array.prototype.some.call(checkboxes, function(chk) {
                return chk.checked;
            });
            if (!anyChecked) {
                e.preventDefault();
                alert('<?= __('Pilih minimal satu kolom untuk diekspor!') ?>');
                return false;
            }
        });
    }
})();
</script>
