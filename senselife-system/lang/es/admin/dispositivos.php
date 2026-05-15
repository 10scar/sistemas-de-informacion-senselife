<?php

return [
    'breadcrumb'        => 'Dispositivos',
    'title'             => 'Dispositivos',
    'subtitle'          => 'Todos los dispositivos SenseLife registrados en el sistema. Haz clic en uno para ver su detalle completo.',
    'tag'               => 'HU-002',
    'cta_new'           => 'Nuevo Dispositivo',

    'stats' => [
        'total'       => 'Total de dispositivos',
        'en_uso'      => 'En uso',
        'sin_asignar' => 'Sin asignar / inactivos',
    ],

    'filters' => [
        'search_placeholder'    => 'Buscar por ID, modelo o centro...',
        'all_states'            => 'Todos los estados',
        'all_centers'           => 'Todos los centros',
        'state_activo'          => 'Activo',
        'state_mantenimiento'   => 'Mantenimiento',
        'state_inactivo'        => 'Inactivo',
    ],

    'table' => [
        'id'     => 'ID Dispositivo',
        'modelo' => 'Modelo',
        'centro' => 'Centro Médico',
        'estado' => 'Estado',
    ],

    'estado' => [
        'activo'        => 'Activo',
        'mantenimiento' => 'Mantenimiento',
        'inactivo'      => 'Inactivo',
    ],

    'pagination_summary' => 'Mostrando :shown de :total dispositivos',
    'empty'              => 'No hay dispositivos que coincidan con los filtros aplicados.',
];
