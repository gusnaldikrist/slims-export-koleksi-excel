<?php
/**
 * Registry kolom + builder SELECT/WHERE untuk ekspor koleksi.
 * Single source of truth untuk form, SELECT, header, dan whitelist.
 * Mendukung agregasi satu baris per judul (biblio) dengan ringkasan eksemplar fisik & stok ketersediaan.
 * 
 * Author: Gusnaldi Kristiadi Syah
 */

if (!function_exists('__')) {
    function __($text) {
        return $text;
    }
}

$REGISTRY = array(
    // 0. Kolom Nomor Urut
    'row_no'          => array('label' => __('No.'), 'sql' => null, 'alias' => 'row_no', 'type' => 'row_number', 'width' => 6),

    // 1. Kolom Bibliografi / Katalog
    'title'           => array('label' => __('Judul'), 'sql' => 'b.title', 'alias' => 'title', 'type' => 'string', 'width' => 42),
    'authors'         => array('label' => __('Pengarang'), 'sql' => 'au.author_names', 'alias' => 'authors', 'type' => 'string', 'width' => 28),
    'publisher'       => array('label' => __('Penerbit'), 'sql' => 'mp.publisher_name', 'alias' => 'publisher', 'type' => 'string', 'width' => 25),
    'publish_year'    => array('label' => __('Tahun Terbit'), 'sql' => 'b.publish_year', 'alias' => 'publish_year', 'type' => 'string', 'width' => 14),
    'publish_place'   => array('label' => __('Tempat Terbit'), 'sql' => 'mpl.place_name', 'alias' => 'publish_place', 'type' => 'string', 'width' => 18),
    'isbn_issn'       => array('label' => __('ISBN/ISSN'), 'sql' => 'b.isbn_issn', 'alias' => 'isbn_issn', 'type' => 'string', 'width' => 20),
    'classification'  => array('label' => __('Nomor Klasifikasi'), 'sql' => 'b.classification', 'alias' => 'classification', 'type' => 'string', 'width' => 18),
    'call_number'     => array('label' => __('Nomor Panggil'), 'sql' => 'b.call_number', 'alias' => 'call_number', 'type' => 'string', 'width' => 20),
    'edition'         => array('label' => __('Edisi'), 'sql' => 'b.edition', 'alias' => 'edition', 'type' => 'string', 'width' => 14),
    'language'        => array('label' => __('Bahasa'), 'sql' => 'ml.language_name', 'alias' => 'language', 'type' => 'string', 'width' => 14),
    'gmd'             => array('label' => __('GMD'), 'sql' => 'mg.gmd_name', 'alias' => 'gmd', 'type' => 'string', 'width' => 16),

    // 2. Kolom Eksemplar, Stok & Ketersediaan
    'total_items'     => array(
        'label' => __('Total Eksemplar'), 
        'sql' => 'COUNT(DISTINCT i.item_id)', 
        'alias' => 'total_items', 
        'type' => 'numeric',
        'width' => 16
    ),
    'available_items' => array(
        'label' => __('Tersedia di Rak'), 
        'sql' => "COUNT(DISTINCT CASE WHEN l.loan_id IS NULL AND (i.item_status_id IS NULL OR i.item_status_id = '' OR i.item_status_id = '0') AND i.item_id IS NOT NULL THEN i.item_id END)", 
        'alias' => 'available_items', 
        'type' => 'numeric',
        'width' => 16
    ),
    'loaned_items'    => array(
        'label' => __('Sedang Dipinjam'), 
        'sql' => "COUNT(DISTINCT CASE WHEN l.loan_id IS NOT NULL THEN i.item_id END)", 
        'alias' => 'loaned_items', 
        'type' => 'numeric',
        'width' => 16
    ),
    'other_items'     => array(
        'label' => __('Rusak/Hilang/Khusus'), 
        'sql' => "COUNT(DISTINCT CASE WHEN i.item_status_id IS NOT NULL AND i.item_status_id != '' AND i.item_status_id != '0' AND l.loan_id IS NULL THEN i.item_id END)", 
        'alias' => 'other_items', 
        'type' => 'numeric',
        'width' => 20
    ),
    'item_codes'      => array(
        'label' => __('Daftar Barcode Eksemplar'), 
        'sql' => "GROUP_CONCAT(DISTINCT i.item_code ORDER BY i.item_code SEPARATOR '; ')", 
        'alias' => 'item_codes', 
        'type' => 'string',
        'width' => 36
    ),
    'locations'       => array(
        'label' => __('Lokasi Rak'), 
        'sql' => "GROUP_CONCAT(DISTINCT mloc.location_name SEPARATOR '; ')", 
        'alias' => 'locations', 
        'type' => 'string',
        'width' => 22
    ),
    'coll_types'      => array(
        'label' => __('Tipe Koleksi'), 
        'sql' => "GROUP_CONCAT(DISTINCT mc.coll_type_name SEPARATOR '; ')", 
        'alias' => 'coll_types', 
        'type' => 'string',
        'width' => 20
    ),
    'item_statuses'   => array(
        'label' => __('Status Eksemplar'), 
        'sql' => "GROUP_CONCAT(DISTINCT mis.item_status_name SEPARATOR '; ')", 
        'alias' => 'item_statuses', 
        'type' => 'string',
        'width' => 20
    ),
    'inventory_codes' => array(
        'label' => __('Kode Inventaris'), 
        'sql' => "GROUP_CONCAT(DISTINCT i.inventory_code SEPARATOR '; ')", 
        'alias' => 'inventory_codes', 
        'type' => 'string',
        'width' => 24
    ),
);

function buildSelect(array $chosen): string
{
    global $REGISTRY;
    $parts = array();
    foreach ($REGISTRY as $token => $col) {
        if (in_array($token, $chosen, true)) {
            if (!empty($col['sql'])) {
                $parts[] = $col['sql'] . ' AS ' . $col['alias'];
            }
        }
    }
    return implode(', ', $parts);
}

function buildWhere(array $g): array
{
    $clauses = array();
    $params = array();

    $from = isset($g['date_from']) ? trim((string)$g['date_from']) : '';
    $to = isset($g['date_to']) ? trim((string)$g['date_to']) : '';
    $error = null;
    if (($from === '') !== ($to === '')) {
        $error = 'Rentang tanggal tidak lengkap: date_from dan date_to harus diisi bersamaan.';
    } elseif ($from !== '' && $to !== '') {
        $dFrom = DateTime::createFromFormat('!Y-m-d', $from);
        $dTo = DateTime::createFromFormat('!Y-m-d', $to);
        if ($dFrom === false || $dTo === false || $dFrom->format('Y-m-d') !== $from || $dTo->format('Y-m-d') !== $to) {
            $error = 'Format tanggal tidak valid, gunakan YYYY-MM-DD.';
        } elseif ($from > $to) {
            $error = 'Tanggal mulai harus sebelum atau sama dengan tanggal selesai.';
        }
    }
    if ($error !== null) {
        return array('sql' => '', 'params' => array(), 'error' => $error);
    }
    if ($from !== '' && $to !== '') {
        $dateClause = 'b.input_date BETWEEN :d1 AND :d2';
        if (!empty($g['include_null_date'])) {
            $dateClause = '(' . $dateClause . ' OR b.input_date IS NULL)';
        }
        $clauses[] = $dateClause;
        $params[':d1'] = $from . ' 00:00:00';
        $params[':d2'] = $to . ' 23:59:59';
    }

    if (isset($g['location']) && (string)$g['location'] !== '') {
        $clauses[] = 'i.location_id = :location';
        $params[':location'] = (string)$g['location'];
    }
    if (isset($g['coll_type']) && (string)$g['coll_type'] !== '') {
        $clauses[] = 'i.coll_type_id = :coll_type';
        $params[':coll_type'] = (string)$g['coll_type'];
    }
    if (isset($g['item_status']) && (string)$g['item_status'] !== '') {
        $clauses[] = 'i.item_status_id = :item_status';
        $params[':item_status'] = (string)$g['item_status'];
    }
    if (isset($g['class_prefix']) && trim((string)$g['class_prefix']) !== '') {
        $prefix = str_replace(array('\\', '%', '_'), array('\\\\', '\\%', '\\_'), trim((string)$g['class_prefix']));
        $clauses[] = "b.classification LIKE CONCAT(:prefix,'%') ESCAPE '\\\\'";
        $params[':prefix'] = $prefix;
    }

    return array('sql' => implode(' AND ', $clauses), 'params' => $params, 'error' => null);
}
