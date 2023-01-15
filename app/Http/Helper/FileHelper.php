<?php

namespace App\Http\Helper;


use App\Http\Repository\FileRepository;
use Illuminate\Support\Facades\Storage;

class FileHelper
{
    /**
     * returns user root directory
     *
     * @param int $userId
     * @return string
     */
    public static function getUserDir(int $userId) : string
    {
        return 'id_' . $userId . '/';
    }

    /**
     * returns file's directory
     *
     * @param int $userId
     * @param string $folder
     * @return string
     */
    public static function getFileDir(int $userId, string $folder = '') : string
    {
        return empty($folder) ? self::getUserDir($userId) : self::getUserDir($userId) . "$folder/";
    }

    /**
     * returns file's path
     *
     * @param int $userId
     * @param string $fileName
     * @param string $folder
     * @return string
     */
    public static function getFilePath(int $userId, string $fileName, string $folder = '') : string
    {
        return self::getFileDir($userId, $folder) . "$fileName";
    }

    /**
     * returns absolute directory path
     *
     * @param int $userId
     * @param string $folder
     * @return string
     */
    public static function getAbsoluteDirPath(int $userId, string $folder = '') : string
    {
        return Storage::disk('local')->path('') . self::getFileDir($userId, $folder);
    }

    /**
     * returns absolute file path
     *
     * @param int $userId
     * @param string $fileName
     * @param string $folder
     * @return string
     */
    public static function getAbsoluteFilePath(int $userId, string $fileName, string $folder = '') : string
    {
        return self::getAbsoluteDirPath($userId, $folder) . $fileName;
    }

    /**
     * returns hashed file name
     *
     * @param string $fullPath
     * @return string
     */
    public static function getHashedFileName(string $fullPath) : string
    {
        preg_match('/(?<=\/|\\\)[\w.]+$/', $fullPath, $matches);
        return $matches[0];
    }

    /**
     * check is file have php extension
     *
     * @param string $fileName
     * @return bool
     */
    public static function isPhp(string $fileName) : bool
    {
        return (bool) preg_match('/(\.php)$/i', $fileName);
    }

    /**
     * checks size limit for upload file
     *
     * @param int $userId
     * @param int $fileSize
     * @return bool
     */
    public static function isExceededLimit(int $userId, int $fileSize) : bool
    {
        return FileRepository::getFilesSize($userId) + $fileSize > 104857600;
    }

    /**
     * checks is file exists
     *
     * @param int $userId
     * @param string $originalFileName
     * @param string $folder
     * @return bool
     */
    public static function isFileExists(int $userId, string $originalFileName, string $folder = '') : bool
    {
        return !empty(FileRepository::getFileByUser($userId, $originalFileName, $folder));
    }
}
