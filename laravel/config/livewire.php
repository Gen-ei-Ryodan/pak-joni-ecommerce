<?php

return [
    'temporary_file_upload' => [
        'disk' => 'local',
        'directory' => 'livewire-tmp',
        'rules' => 'max:5120',
        'middleware' => 'throttle:60,1',
    ],
];
