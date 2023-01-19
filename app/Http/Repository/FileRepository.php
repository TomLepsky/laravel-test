<?php

namespace App\Http\Repository;

use App\Http\Helper\FileHelper;
use App\Models\File;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File as FileFacade;

class FileRepository
{
    /**
     * retrieves file by user id and file name
     *
     * @param int $userId
     * @param string $fileName
     * @param string $folder
     * @return File|null
     */
    public static function getFileByUser(int $userId, string $fileName, string $folder = '') : ?File
    {
        return File::where('originalName', $fileName)
            ->where('folder', $folder)
            ->where('userId', $userId)
            ->first();
    }

    /**
     * calculates files size
     *
     * @param int $userId
     * @param string|null $folder
     * @return int
     */
    public static function getFilesSize(int $userId, ?string $folder = null) : int
    {
        $builder = DB::table('files')
            ->where('userId', $userId);
        if (!is_null($folder)) {
            $builder->where('folder', $folder);
        }
        return $builder->sum('size');
    }

    /**
     * retrieves all user's files
     *
     * @param int $userId
     * @return Collection
     */
    public static function getAllUserFiles(int $userId) : Collection
    {
        return File::where('userId', $userId)->get();
    }

    /**
     * retrieves all user's directories
     *
     * @param int $userId
     * @return array
     */
    public static function getAllUserDirs(int $userId) : array
    {
        $dirs = [];
        foreach (FileFacade::directories(FileHelper::getAbsoluteDirPath($userId)) as $dir) {
            preg_match('/(?<=\/)\w+$/', $dir, $matches);
            $dirs[] = $matches[0];
        }
        return $dirs;
    }

}
