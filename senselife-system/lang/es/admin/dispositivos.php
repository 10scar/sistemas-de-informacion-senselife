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

    'create_modal' => [
        'title'              => 'Agregar dispositivo',
        'subtitle'           => 'Nuevo dispositivo SenseLife en el sistema',
        'close_aria'         => 'Cerrar',
        'section_id'         => 'Identificación del dispositivo',
        'section_assign'     => 'Asignación',
        'modelo_label'       => 'Modelo',
        'modelo_placeholder' => 'Seleccionar modelo…',
        'serie_label'        => 'Número de serie',
        'serie_placeholder'  => 'Formato: [modelo]-[año]-[número correlativo de 4 dígitos].',
        'centro_label'       => 'Centro médico',
        'centro_placeholder' => 'Seleccionar centro médico…',
        'centro_help'        => 'El dispositivo quedará disponible en ese centro para ser asignado a un paciente.',
        'estado_label'       => 'Estado inicial',
        'info'               => 'El ID del dispositivo se generará automáticamente al guardar. La asignación a un paciente se realiza desde la vista del centro médico correspondiente.',
        'cancel'             => 'Cancelar',
        'submit'             => 'Registrar dispositivo',
        'created'            => 'Dispositivo registrado correctamente.',
    ],

    'edit_modal' => [
        'title'    => 'Editar dispositivo',
        'subtitle' => 'Actualiza la información del dispositivo SenseLife',
        'submit'   => 'Actualizar dispositivo',
    ],

    'detail_modal' => [
        'title'        => 'Detalle del dispositivo',
        'subtitle'     => 'Información registrada del dispositivo SenseLife',
        'section_id'   => 'Identificación del dispositivo',
        'section_assign' => 'Asignación',
        'id_label'     => 'ID interno',
        'modelo_label' => 'Modelo',
        'serie_label'  => 'Número de serie',
        'centro_label' => 'Centro médico',
        'estado_label' => 'Estado',
        'close'        => 'Cerrar',
        'edit'         => 'Editar dispositivo',
        'empty_value'  => '—',
    ],

    'success_modal' => [
        'title'     => 'Se registró correctamente el dispositivo',
        'created'   => 'Se registró correctamente el dispositivo',
        'updated'   => 'Datos actualizados',
        'continue'  => 'Continuar',
        'icon_aria' => 'Operación exitosa',
    ],
];
