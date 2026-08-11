<?php

return [
    // Production servers normally expose these binaries through PATH. Local
    // environments can still override both values in .env with absolute paths.
    'ffmpeg_binary' => env('FFMPEG_BINARY', 'ffmpeg'),
    'ffprobe_binary' => env('FFPROBE_BINARY', 'ffprobe'),
    'disk' => env('MEDIA_DISK', 'public'),
    'cdn_url' => env('MEDIA_CDN_URL'),
    'max_upload_kb' => (int) env('VIDEO_MAX_UPLOAD_KB', 512000),
];
