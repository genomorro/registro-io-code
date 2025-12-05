<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@hotwired/turbo' => [
        'version' => '7.3.0',
    ],
    'tom-select' => [
        'version' => '2.4.3',
    ],
    '@orchidjs/sifter' => [
        'version' => '1.1.0',
    ],
    '@orchidjs/unicode-variants' => [
        'version' => '1.1.2',
    ],
    'tom-select/dist/css/tom-select.default.min.css' => [
        'version' => '2.4.3',
        'type' => 'css',
    ],
    'tom-select/dist/css/tom-select.default.css' => [
        'version' => '2.4.3',
        'type' => 'css',
    ],
    'tom-select/dist/css/tom-select.bootstrap4.css' => [
        'version' => '2.4.3',
        'type' => 'css',
    ],
    'tom-select/dist/css/tom-select.bootstrap5.css' => [
        'version' => '2.4.3',
        'type' => 'css',
    ],
    'bootstrap' => [
        'version' => '5.3.8',
    ],
    '@popperjs/core' => [
        'version' => '2.11.8',
    ],
    'bootstrap/dist/css/bootstrap.min.css' => [
        'version' => '5.3.8',
        'type' => 'css',
    ],
    'datatables.net-bs5' => [
        'version' => '2.3.4',
    ],
    'jquery' => [
        'version' => '3.7.1',
    ],
    'datatables.net' => [
        'version' => '2.3.4',
    ],
    'datatables.net-bs5/css/dataTables.bootstrap5.min.css' => [
        'version' => '2.3.4',
        'type' => 'css',
    ],
    'datatables.net-buttons' => [
        'version' => '3.2.5',
    ],
    'datatables.net-buttons-bs5' => [
        'version' => '3.2.5',
    ],
    'datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css' => [
        'version' => '3.2.5',
        'type' => 'css',
    ],
    'datatables.net-buttons-dt' => [
        'version' => '3.2.5',
    ],
    'datatables.net-dt' => [
        'version' => '2.3.4',
    ],
    'datatables.net-buttons-dt/css/buttons.dataTables.min.css' => [
        'version' => '3.2.5',
        'type' => 'css',
    ],
    'datatables.net-dt/css/dataTables.dataTables.min.css' => [
        'version' => '2.3.4',
        'type' => 'css',
    ],
    'datatables.net-columncontrol-bs5' => [
        'version' => '1.1.1',
    ],
    'datatables.net-columncontrol' => [
        'version' => '1.1.1',
    ],
    'datatables.net-columncontrol-bs5/css/columnControl.bootstrap5.min.css' => [
        'version' => '1.1.1',
        'type' => 'css',
    ],
    'datatables.net-columncontrol-dt' => [
        'version' => '1.1.1',
    ],
    'datatables.net-columncontrol-dt/css/columnControl.dataTables.min.css' => [
        'version' => '1.1.1',
        'type' => 'css',
    ],
    'datatables.net-buttons/js/buttons.colVis' => [
        'version' => '3.2.5',
    ],
    'datatables.net-buttons/js/buttons.html5' => [
        'version' => '3.2.5',
    ],
    'datatables.net-buttons/js/buttons.print' => [
        'version' => '3.2.5',
    ],
    'datatables.net-select-bs5' => [
        'version' => '3.1.3',
    ],
    'datatables.net-select' => [
        'version' => '3.1.3',
    ],
    'datatables.net-select-bs5/css/select.bootstrap5.min.css' => [
        'version' => '3.1.3',
        'type' => 'css',
    ],
    'datatables.net-select-dt' => [
        'version' => '3.1.3',
    ],
    'datatables.net-select-dt/css/select.dataTables.min.css' => [
        'version' => '3.1.3',
        'type' => 'css',
    ],
    'datatables.net-responsive-bs5' => [
        'version' => '3.0.7',
    ],
    'datatables.net-responsive' => [
        'version' => '3.0.7',
    ],
    'datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css' => [
        'version' => '3.0.7',
        'type' => 'css',
    ],
    'datatables.net-responsive-dt' => [
        'version' => '3.0.7',
    ],
    'datatables.net-responsive-dt/css/responsive.dataTables.min.css' => [
        'version' => '3.0.7',
        'type' => 'css',
    ],
    'jszip' => [
        'version' => '3.10.1',
    ],
    'pdfmake' => [
        'version' => '0.2.20',
    ],
    'pdfmake/build/vfs_fonts' => [
        'version' => '0.2.20',
    ],
];
