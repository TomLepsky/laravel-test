<?php

namespace App\Http\Controllers;

use App\Http\Helper\ApiHelper;
use App\Http\Helper\FileHelper;
use App\Http\Repository\FileRepository;
use App\Policies\FilePolicy;
use App\Rules\FileLimit;
use App\Rules\NotPHP;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use \App\Models\File as FileModel;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileController extends Controller
{
    /**
     * Create folder to store files
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createFolder(Request $request) : JsonResponse
    {
        $data = $request->validate([
            'folder' => ['required', 'string', 'min:1', 'max:20', 'regex:/^[\w]+$/']
        ]);

        $path = FileHelper::getAbsoluteDirPath($request->user()->id, $data['folder']);
        if (File::exists($path)) {
            return ApiHelper::response(400, null, 'directory already exists');
        }
        if (!File::makeDirectory($path, 0777)) {
            return response()->json([
                'message' => 'couldn\'t create folder'
            ], 500);
        }
        return ApiHelper::response(201);
    }

    /**
     * upload file via multipart-form
     *  file - uploading file
     *  data - {"name": "file_name", "folder": "folder_name"}
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function upload(Request $request) : JsonResponse
    {
        if (!$request->hasFile('file')) {
            return ApiHelper::response(400, message: 'file was not uploaded');
        }

        if ($request->has('data')) {
            $data = json_decode($request->data, true);
            if ($data !== null) {
                $request->merge($data);
            }
        }

        $data = $request->validate([
            'file' => ['required', 'file', 'max:20480', new NotPHP(), new FileLimit()],
            'name' => ['string', 'min:1', 'max:20', 'not_regex:/\.php$/'],
            'folder' => ['string', 'min:1', 'max:20', 'regex:/^[\w]+$/']
        ]);

        $file = $request->file('file');
        $userId = $request->user()->id;

        $originalFileName = $data['name'] ?? $file->getClientOriginalName();

        $folder = '';
        if (isset($data['folder'])) {
            $folder = $data['folder'];
        }

        if (FileHelper::isFileExists($userId, $originalFileName, $folder)) {
            return ApiHelper::response(400, message: 'file with this name already exists');
        }

        if (($hashedName = $file->store(FileHelper::getFileDir($userId, $folder))) === false) {
            return ApiHelper::response(500, message: 'Couldn\'t upload file');
        }

        $fileModel = new FileModel();
        $fileModel->userId = $userId;
        $fileModel->name = FileHelper::getHashedFileName($hashedName);
        $fileModel->originalName = $originalFileName;
        $fileModel->size = $file->getSize();
        $fileModel->folder = $data['folder'] ?? '';
        $fileModel->save();

        return ApiHelper::response(201);
    }

    /**
     * delete file
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function delete(Request $request, int $id) : JsonResponse
    {
        $file = FileModel::find($id);
        if ($file === null) {
            return ApiHelper::response(404, message: 'File not found');
        }

        if (Gate::denies(FilePolicy::DELETE, $file)) {
            return ApiHelper::response(403, message: 'Access denied');
        }

        if (!$file->delete()) {
            return ApiHelper::response(500, message: 'Couldn\'t delete file');
        }

        $userId = $request->user()->id;
        if (!Storage::disk('local')->delete(FileHelper::getFilePath($userId, $file->name, $file->folder))) {
            $file->save();
            return ApiHelper::response(500, message: 'Couldn\'t delete file');
        }

        return ApiHelper::response(200);
    }


    /**
     * rename file
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function rename(Request $request, int $id) : JsonResponse
    {
        $data = $request->validate([
            'newName' => ['required', 'string', 'min:1', 'max:20', 'not_regex:/[\\/]+/i'],
        ]);

        $newName = $data['newName'];
        $userId = $request->user()->id;

        $file = FileModel::find($id);
        if ($file === null) {
            return ApiHelper::response(404, message: 'File not found');
        }

        if (Gate::denies(FilePolicy::PATCH, $file)) {
            return ApiHelper::response(403, message: 'Access denied');
        }

        if (FileHelper::isFileExists($userId, $newName, $file->folder)) {
            return ApiHelper::response(400, message: 'File with this name already exists');
        }

        $file->originalName = $newName;
        $file->save();

        return ApiHelper::response(200);
    }

    /**
     * download file
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse|BinaryFileResponse
     */
    public function download(Request $request, int $id) : JsonResponse|BinaryFileResponse
    {
        $file = FileModel::find($id);
        if ($file === null) {
            return ApiHelper::response(404, message: 'File not found');
        }

        if (Gate::denies(FilePolicy::DOWNLOAD, $file)) {
            return ApiHelper::response(403, message: 'Access denied');
        }

        $userId = $request->user()->id;
        return response()->download(FileHelper::getAbsoluteFilePath($userId, $file->name, $file->folder), $file->originalName);
    }

    /**
     * show all user's files
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request) : JsonResponse
    {
        $files = FileRepository::getAllUserFiles($request->user()->id);
        return ApiHelper::response(200, [$files]);
    }

    /**
     * Calculate files size
     *
     * @param Request $request
     * @param string|null $folder
     * @return JsonResponse
     */
    public function size(Request $request, ?string $folder = null) : JsonResponse
    {
        return ApiHelper::response(200, ['size' => FileRepository::getFilesSize($request->user()->id, $folder)]);
    }

}
