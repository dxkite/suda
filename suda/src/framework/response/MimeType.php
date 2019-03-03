<?php
namespace suda\framework\response;

/**
 * 响应MIME
 */
class MimeType
{
    /**
     * 内置Mime数组
     *
     * @var array
     */
    protected static $mimes = [
        # Image Type
        'jpe' => 'image/jpeg' ,
        'jpeg' => 'image/jpeg' ,
        'jpg' => 'image/jpeg' ,
        'svg' => 'image/svg+xml' ,
        'png' => 'image/png' ,
        'gif' => 'image/gif' ,
        'ico' => 'image/x-icon',
        'webp' => 'image/webp',

        # Text Type
        'js' => 'text/javascript',
        'css' => 'text/css',
        'txt' => 'text/plain',
        'html' => 'text/html' ,
        'csv' => 'text/csv' ,
        'xml' => 'text/xml',

        # App type
        'json' => 'application/json',
        'pdf' => 'application/pdf',
        'rss' => 'application/rss+xml',
        'rtf' => 'application/rtf',
        'apk' => 'application/vnd.android.package-archive',

        # Archive
        'zip' => 'application/zip',
        'gtar' => 'application/x-gtar' ,
        'gz' => 'application/x-gzip' ,
        '7z' => 'application/x-7z-compressed',
        'rar' => 'application/x-rar-compressed',

        # Office
        'dot' => 'application/msword',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'pps' => 'application/vnd.ms-powerpoint',
        'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',

        # Audio
        'wav' => 'audio/wav',
        'mp3' => 'audio/mp3',
        'ogg' => 'audio/ogg',
        'm4a' => 'audio/x-m4a',

        # Video
        'avi' => 'video/avi',
        'mp4' => 'video/mp4',
        'flv' => 'video/x-flv',
        'webm' => 'video/webm',
        'm4v' => 'video/x-m4v',
        '3gp' => 'video/3gpp',
    ];


    public static function getMimeType(string $extension)
    {
        return static::$mimes[$extension] ?? 'application/octet-stream';
    }
}
