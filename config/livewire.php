<?php

return [
    'temporary_file_upload' => [
        'disk' => 'local',
        'directory' => 'livewire-tmp',
        'rules' => 'max:102400',
        'middleware' => 'throttle:60,1',
    ],
];
