<?php

/**
 * php artisan route:list --name=api
 * php artisan make:doc {key} {route_name}
 * php artisan make:doc {key} {route_group_name} --resource
 * php artisan doc:generate {documentation?}
 */
return [
    'documentations' => [
        [
            'key' => 'api',
            'title' => 'API',
            'description' => '',
            'version' => '1.0.0',
            'default_security' => [],
            'path_doc_json' => storage_path('docs/api/api.json'),
            'path_components' => storage_path('docs/api/components/'),
            'path_routes' => storage_path('docs/api/routes/'),
            'names' => [
                //'users.index',
                //'users.show',
                //'users.store',
                //'users.update',
                //'users.destroy',
            ],
            'generators' => [],
            'creators' => [],
        ],
    ],
];
