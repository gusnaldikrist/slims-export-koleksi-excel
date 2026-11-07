<?php
/**
 * Plugin Name: Ekspor Koleksi & Eksemplar (Excel)
 * Plugin URI: https://github.com/gusnaldikrist/slims-export-koleksi-excel
 * Description: Ekspor data bibliografi dan ringkasan eksemplar per judul koleksi ke file Excel (.xlsx) profesional dari menu Reporting.
 * Version: 1.0.0
 * Author: Gusnaldi Kristiadi Syah
 * Author URI: https://github.com/gusnaldikrist
 */

use SLiMS\Plugins;

// Idempotency guard: Cegah registrasi ganda jika file dimuat lebih dari sekali oleh SLiMS
$menuLabel = __('Ekspor Koleksi & Eksemplar (Excel)');
$existingMenus = Plugins::getInstance()->getMenus('reporting');
$isAlreadyRegistered = false;

foreach ($existingMenus as $menuItem) {
    if (isset($menuItem[0]) && $menuItem[0] === $menuLabel) {
        $isAlreadyRegistered = true;
        break;
    }
}

if (!$isAlreadyRegistered) {
    Plugins::getInstance()->registerMenu('reporting', $menuLabel, __DIR__ . '/pages/index.php');
}
