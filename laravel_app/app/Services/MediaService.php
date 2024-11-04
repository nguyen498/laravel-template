<?php

namespace App\Services;

use App\Models\Media;
use App\Repositories\Interfaces\MediaRepositoryInterface;
use App\Services\Base\BaseService;
use App\Utils\FileHelpers;

class MediaService extends BaseService
{
    protected $repo_base;
    protected $with;

    public function __construct(
        MediaRepositoryInterface $repo_base
    )
    {
        $this->repo_base = $repo_base;
        $this->with = [];
    }

    public function getModelName()
    {
        return 'Media';
    }

    public function getTableName()
    {
        return (new Media())->getTable();
    }

    public function uploadMedia($request): array
    {
        $inputs = $request->all();
        if (!isset($inputs['file'])) {
            return [
                'code' => '004',
                'message' => 'Hình ảnh'
            ];
        }
        if (isset($inputs['file'])) {
            $path = $this->saveMediaPath($inputs['file'], $inputs['mediaable_type'],
                null,
                $request, "public");
        }
        $media = $this->repo_base->create([
            'mediaable_id' => $inputs['mediaable_id'] ?? null,
            'mediaable_type' => $inputs['mediaable_type'] ?? null,
            'name' => $path['full_name'] ?? null,
            'path' => $path['path'] ?? null,
            'type' => Media::PUBLIC
        ]);

        return [
            'code' => '200',
            'data' => $this->formatData($media)
        ];
    }

    public function delete($id)
    {
        $media = $this->repo_base->findById($id);
        if (!isset($media)) {
            return [
                'code' => '004',
                'message' => 'Hình ảnh'
            ];
        }
        $this->deleteMedia($media);
        return [
            'code' => '200',
            'data' => [],
            'message' => 'Xoá hình ảnh thành công'
        ];
    }

    public function saveMediaPath($file, $savePath, $size, $inputs, $disk = "public", $user_id = "", $mediaName = null, $rotate = null,) {
        $file = $inputs->file('file');

        if ($file->isValid()) {
            // Kiểm tra xem tệp tin là hình ảnh hay không
            $image_info = getimagesize($file->path());

            if ($image_info !== false) {
                // Nếu là hình ảnh
                $path = FileHelpers::updateImageNameAndDynamic($file, $savePath, $size, $disk, $user_id, $mediaName, $rotate);
            } else {
                // Nếu không phải là hình ảnh
                return ['is_failed' => true, 'code' => '090', 'message' => 'Hình ảnh không hợp lệ'];
            }
        } else {
            // Xử lý khi tệp tin không hợp lệ
            return ['is_failed' => true, 'code' => '090', 'message' => 'Hình ảnh không hợp lệ'];
        }


        return $path;
    }

    public function deleteMedia($media)
    {
        // remove all images first
        FileHelpers::removeFilePath($media->getOriginal('path'));
        $media->delete();
    }

    public function formatData($data)
    {
        $res = json_decode($data, true);
        $res['path'] = env("DOMAIN_IMAGE_URL", "http://localhost:4050") . "/storage/{$data->path}";
        $res['path_without_domain'] = $data->path;
        return $res;
    }
}
