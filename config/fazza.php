<?php

return [
    'base_url'        => env('FAZZA_BASE_URL', 'https://staging.fazza3.app'),
    'oauth_url'       => env('FAZZA_OAUTH_URL', 'https://staging.fazza3.app/oauth/token'),
    'client_id'       => env('FAZZA_CLIENT_ID', '11'),
    'client_secret'   => env('FAZZA_CLIENT_SECRET', 'rGfBA6wjUBst3a9DG3GCbrKM9X1y5m7fM1dqDvkN'),
    'grant_type'      => env('FAZZA_GRANT_TYPE', 'client_credentials'),

    'timeout'         => 15,
    'retries'         => 2,
    'retry_delay_ms'  => 500,
];


 