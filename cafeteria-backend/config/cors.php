<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

<<<<<<< HEAD
    'allowed_origins' => [
        env('FRONTEND_URL', 'http://localhost:5173'),
        'https://*.vercel.app',
    ],
=======
    // ⚠️ IMPORTANTE: no usar '*'
    'allowed_origins' => ['http://localhost:5173'],
>>>>>>> respaldo-local

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

<<<<<<< HEAD
=======
    // 🔥 CLAVE para withCredentials
>>>>>>> respaldo-local
    'supports_credentials' => true,

];
