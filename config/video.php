<?php

return [
    'ffmpeg_binary' => env('FFMPEG_BINARY', 'D:\\Tools\\FFmpeg\\bin\\ffmpeg.exe'),
    'ffprobe_binary' => env('FFPROBE_BINARY', 'D:\\Tools\\FFmpeg\\bin\\ffprobe.exe'),
    'disk' => env('MEDIA_DISK', 'public'),
    'cdn_url' => env('MEDIA_CDN_URL'),
];
