<?php
/**
 * Created by PhpStorm.
 * User: tuan
 * Date: 10/16/17
 * Time: 4:42 PM
 */

namespace App\Utils;


use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

class FileHelpers
{
    public function removeViewCache(){
        $files = Storage::disk('view_cached')->allFiles();
        foreach($files as $file){ // iterate files
            if(is_file($file))
                unlink($file); // delete file
        }
    }

    public function removeFile($request, $path){
        $fileName = $request->fileName;
        // delete file if it exist
        unlink($path .'/' . $fileName);
        // delete path if empty
        if($this->is_dir_empty($path)){
            rmdir($path);
        }
    }

    private function getDateTime(){
        $current = Carbon::now();
        return $current->day .
            $current->month .
            $current->year .
            $current->hour .
            $current->minute .
            $current->second .
            $current->micro;
    }

    private function is_dir_empty($dir) {
        if (!is_readable($dir)) return NULL;
        return (count(scandir($dir)) == 2);
    }

    public function checkFileExtension($file){
        if(!empty($file)){
            $name = $file->getClientOriginalName();
            $split = explode('.', $name);
            if(!isset($split[1]) || ($split[1] != 'xlsx' && $split[1] != 'xls' && $split[1] != 'csv')){
                return false;
            }
            return true;
        }
        return false;
    }

    public function uploadFile($file, $path){
        if(!empty($file)){
//            if (!is_dir($path)) {
//                mkdir($path, 0777, true);
//            }
//            $name = $file->hashName();
            $data = $this->getUploadFileName($file, $path);

            Storage::disk('public')->put($path . '/' . $data['name'], File::get($file));
            return url('/') . '/storage/' . $path . '/' . $data['name'];
        }
        return null;
    }

    public function uploadFileName($file, $path){
        if(!empty($file)){
//            if (!is_dir($path)) {
//                mkdir($path, 0777, true);
//            }
//            $name = $file->hashName();
            $data = $this->getUploadFileName($file, $path);
            Storage::disk('public')->put($path . '/' . $data['name'], File::get($file));
            return [

                'path' => url('/') . '/storage/' . $path . '/' . $data['name'],
                'name' => $data['name'],
                'original' => $data['original']
            ];
        }
        return null;
    }

    public function checkFileExist($path, $name) {
        $check_name = $name . '.jpg';
        $check_path = storage_path('app/public') . '/' . $path;
        if (file_exists($check_path . '/' . $check_name)) {
            return url('/') . '/storage/' . $path . '/' . $check_name;
        }
        return null;
    }

    public function uploadFileFromUrl($url, $path, $name){
        $name = $this->getUploadFileNameUrl($name, $path);

        try {
            Image::configure(['driver' => 'imagick']);
            $img = Image::make($url);
            // resize only if have data
            $img->encode('jpg', config('constants.image_quality'));

            Storage::disk('public')->put($path . '/' . $name, $img);

            return [
                'path' => 'storage/' . $path . '/' . $name,
                'name' => $name
            ];
        } catch(\Exception $e){
            LogHelper::writeLog('không thể lưu avatar ' . $e->getMessage(), 0);
            return null;
        }
    }

    private function getUploadFileNameUrl($name, $path) {
        $spl = explode('.', $name);
        $name = str_slug($spl[0], '_') . '.jpg';
        $i=1;
        while(file_exists(storage_path('app/public/') . $path . "/" . $name)){
            if($i == 1){
                $spl = explode('.', $name);
                $name=$spl[0] ."_" . $i . '.jpg';
            } else {
                $spl = explode('.', $name);
                $end = strrpos($spl[0], '_');
                $name_1 = substr($spl[0], 0, $end);
                $name= $name_1 ."_" . $i . '.jpg';
            }
            $i++;
        }
        return $name;
    }

    private function getUploadFileName($file, $path) {
        $name = $file->getClientOriginalName();
        $spl = explode('.', $name);

        if(sizeof($spl) > 0){
            $name = str_slug($spl[0], '_') . '.' . $spl[1];
        } else {
            $name .= '.pdf';
        }

        $original = $name;
        $i=1;
        while(file_exists(storage_path('app/public/') . $path . "/" . $name)){
            if($i == 1){
                $spl = explode('.', $name);
                $name= $spl[0] ."_" . $i . '.' . $spl[1];
            } else {
                $spl = explode('.', $name);
                $end = strrpos($spl[0], '_');
                $name_1 = substr($spl[0], 0, $end);
                $name= $name_1 ."_" . $i . '.' . $spl[1];
            }
            $i++;
        }
        return [
            'original' => $original,
            'name' => $name
        ];
    }

    public function uploadImage($request, $path){
        $file = $request->file('file');
        if(empty($file)){
            $file = $request->file('upload');
        }

        if(!empty($file)){
            Image::configure(['driver' => 'imagick']);
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }
            $name = $file->getClientOriginalName();
//            $file->move($path, $name);
            $img = Image::make($file->getRealPath());
            $img->save($path . '/' . $name);
//            return url('/') . '/' . $path . $name . '?t=' . $this->getDateTime();
            return url('/') . '/' . $path . $name;
        }
        return null;
    }
    // image Invention

    public static function updateImageWithNameAndSize($file, $path, $size){

        if(!empty($file)){
            $name = $file->hashName();
            $name = static::getJpgFileName($name);
            Image::configure(['driver' => 'imagick']);
            if(isset($size)){
                $img = \Image::make($file->getRealPath())
                    ->resize($size['width'], null, function ($constraint) {
                        $constraint->aspectRatio();
                    })->encode('jpg', config('constants.image_quality'));
            } else {
                $img = Image::make($file->getRealPath())->encode('jpg', config('constants.image_quality'));
            }

            Storage::disk('public')->put($path . '/' . $name, $img);
            return [
                'path' => $path . '/' . $name,
                'name' => $name
            ];
        }
        return [
            'name' => '',
            'path' => ''
        ];
    }

    public static function updateImageNameAndFit($file, $path, $size){
        if(!empty($file)){
            $name = time() . '_' . $file->hashName();
            $name = static::getJpgFileName($name);
            Image::configure(['driver' => 'imagick']);
//            $name = time() . '.jpg';
            if(isset($size)){
                if($size['width'] > 0 && $size['height'] > 0){
                    $img = Image::make($file->getRealPath())
                        ->fit($size['width'], $size['height'])
                        ->crop($size['width'], $size['height'], 0, 0)
                        ->encode('jpg', config('constants.image_quality')); //->save(Storage::disk('public') .'/' .$path . '/' . $name);
                } else if($size['width'] > 0){
                    $img = Image::make($file->getRealPath())
                        ->resize($size['width'], null, function ($constraint) {
                            $constraint->aspectRatio();
                        })->encode('jpg', config('constants.image_quality'));
                } else if($size['height'] > 0){
                    $img = Image::make($file->getRealPath())
                        ->resize(null, $size['height'], function ($constraint) {
                            $constraint->aspectRatio();
                        })->encode('jpg', config('constants.image_quality'));
                }
            } else {
                $data = Image::make($file->getRealPath());
                $img = $data->encode('jpg', config('constants.image_quality'));
            }

            Storage::disk('public')->put($path . '/' . $name, $img);
            return [
                'path' => $path . '/' . $name,
                'name' => $name,
                'full_name' => static::getJpgFileName($file->getClientOriginalName())
            ];
        }
        return null;
    }

    public static function updateImageNameAndDynamic($file, $path, $size, $disk = 'public', $user_id = "", $mediaName = null, $rotate = null){
        $user_id_str = "";
        if(isset($user_id) && $user_id !== ""){
            $user_id_str = $user_id . '_';
        }
        if(!empty($file)){
            Image::configure(['driver' => 'imagick']);
            if($mediaName){
                $name = $mediaName;
            }else{
                $name = $user_id_str. time() . '_' . $file->hashName();
            }

//            $name = static::getJpgFileName($name);
//            $name = time() . '.jpg';
            $img = Image::make($file->getRealPath())->orientate();

            if(isset($rotate)){
                $img->rotate($rotate);
            }

            $real_width = $img->width();
            $real_height = $img->height();
            $drop_width = 0;
            $drop_height = 0;

            $size = ['width' => 1080, 'height' => 1080];
            if(isset($size)){
                if($size['width'] > 0 && $size['height'] > 0){
                    if($img->width() > $img->height()){
                        $img->resize($size['width'], null, function ($constraint) {
                            $constraint->aspectRatio();
                        })->encode('jpg', config('constants.image_quality'));
                    } else {
                        $img->resize(null, $size['height'], function ($constraint) {
                            $constraint->aspectRatio();
                        })->encode('jpg', config('constants.image_quality'));
                    }
                } else if($size['width'] > 0){
                    $img->resize($size['width'], null, function ($constraint) {
                        $constraint->aspectRatio();
                    })->encode('jpg', config('constants.image_quality'));
                } else if($size['height'] > 0){
                    $img->resize(null, $size['height'], function ($constraint) {
                        $constraint->aspectRatio();
                    })->encode('jpg', config('constants.image_quality'));
                }
            }
            else {
                $img = Image::make($file->getRealPath())->encode('jpg', config('constants.image_quality'))->orientate();
            }
            $drop_width = $img->width();
            $drop_height = $img->height();
            Storage::disk($disk)->put($path . '/' . $name, $img->stream());
            return [
                'path' => $path . '/' . $name,
                'width' => $real_width,
                'height' => $real_height,
                'drop_width' => $drop_width,
                'drop_height' => $drop_height,
                'name' => $name,
                'full_name' => $file->getClientOriginalName()
            ];
        }
        return null;
    }

    private static function getJpgFileName($name){
        $fileName = '';
        if (strpos($name, '.') !== false) {
            $spl = explode('.', $name);
            $fileName = $spl[0] . '.jpg';
        } else {
            $fileName .= '.jpg';
        }
        return $fileName;
    }

    public static function removeFilePath($path) {
        Storage::disk('public')->delete($path);
    }

    public static function updateImageWithNameAndSizeFromUrl($url, $path, $name, $size){
        $filename = basename($url);
        $extension = self::getExtensionFromUrl($filename);
        if(isset($size['type'])){
            $name = $name . '_' . $size['type'] . '.jpg';
        } else {
            $name = $name . '.' . '.jpg';
        }
        try {
            $img = Image::make($url);
            // resize only if have data
            if(isset($size['width']) && isset($size['height'])){
                if($img->width() > $img->height()){
                    $img->resize($size['width'], null, function ($constraint) {
                        $constraint->aspectRatio();
                    })->encode('jpg', config('constants.image_quality'));
                } else {
                    $img->resize(null, $size['height'], function ($constraint) {
                        $constraint->aspectRatio();
                    })->encode('jpg', config('constants.image_quality'));
                }
//            $img->resize($size['width'], $size['height'])->save($path . '/' . $name);
            } else {
                $img->encode('jpg', config('constants.image_quality'));
            }

            Storage::disk('public')->put($path . '/' . $name, $img);

            return [
                'path' => $path . '/' . $name,
                'name' => $name
            ];
        } catch(\Exception $e){
            LogHelper::writeLog('không thể lưu avatar ' . $e->getMessage(), 0);
            return null;
        }
    }

    public function updateImageFileName($file, $path, $name){
        if(!empty($file)){
//            if (!is_dir($path)) {
//                mkdir($path, 0777, true);
//            }
            $name = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $name = $name . '.' . $extension;
            $img = Image::make($file->getRealPath());
            $img->resize(200, 200)->save($path . '/' . $name);
            $img->save($path . '/' . $name);
            return url('/') . '/'. $path . $name . '?t=' . $this->getDateTime();
        }
        return null;
    }

    public function updatePhotos($file, $path){
        if(!empty($file)){
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }
            $name = $this->getFileName($file);
            $img = Image::make($file->getRealPath());
            $img->save($path . '/' . $name);
            return [
                'name' => $name,
                'path' => $path . '/' . $name
            ];
        }
        return null;
    }

    public function updateStoragePhotos($file, $path){
        if(!empty($file)){
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }
            $name = $this->getFileName($file);
            $img = Image::make($file->getRealPath())->orientate();
            $store  = Storage::put(config('constants.storage_image_path') .'/' . $path.'/'.$name, $img->stream());
            $path    = 'images/' . $path;
            // do not set image name as default
            return [
                'name' => '',
                'path' => $path . '/' . $name,
                'height' => $img->height(),
                'width' => $img->width()
            ];
        }
        return null;
    }

    public function updateAvatarStoragePhotos($file, $path){
        if(!empty($file)){
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }
            $name = $file->getClientOriginalName();
            $img = Image::make($file->getRealPath())->orientate();
            $store  = Storage::put(config('constants.storage_image_path') .'/' . $path.'/'.$name, $img->stream());
            $path    = 'images/' . $path;
            // do not set image name as default
            return [
                'name' => '',
                'path' => $path . '/' . $name,
                'height' => $img->height(),
                'width' => $img->width()
            ];
        }
        return null;
    }

    private function getFileName($file){
//        Log::info($file->getClientOriginalName() . ' - ' . $file->getClientOriginalExtension());
        $extension = $file->getClientOriginalExtension();
        $client_name = $file->getClientOriginalName();
        $client = explode(".", $client_name);
        return $client[0] . '_'. $this->getDateTime() . '.' . $extension;
    }

    private static function getExtensionFromUrl($names){
        if (strpos($names, 'jpg') !== false) {
            return 'jpg';
        } else if (strpos($names, 'png') !== false) {
            return 'png';
        } else if (strpos($names, 'gif') !== false) {
            return 'gif';
        } else if (strpos($names, 'pdf') !== false) {
            return 'pdf';
        }
    }

    public static function getImage ($path){
//        if(isset($user)){
//            $user_id = $user['passenger_id'];
//            $user_phone = $user['phone'];
//            $user_reference = $user['reference'];
//            $object_type = $user['type'];
//        }
//        $media = $this->repo_base->findOneBy([
//            'path' => $path,
//            'user_id' => $user_id,
//            'user_phone' => $user_phone,
//            'user_reference' => $user_reference,
//            'mediaable_type' => $object_type
//        ]);


        // Kiểm tra xem media có tồn tại không
//        if (!$media) {
//            return response()->json(['message' => 'Media not found'], 404);
//        }

        // Lấy đường dẫn file hình ảnh
//        $path = "/private/{$path}";

        // Kiểm tra xem file có tồn tại trong storage không
//        if (!Storage::fileExists($path)) {
//            return response()->json(['message' => 'File not found'], 404);
//        }

        $file_path = storage_path("app/private/{$path}");

        if (file_exists($file_path)) {
            $content = file_get_contents($file_path);
            $type = Storage::mimeType( Storage::disk('private')->get($path));

            // Xử lý nội dung tệp tin ở đây
            return (new Response($content, 200))->header('Content-Type', $type);

//            return $content;
        } else {
            // Xử lý khi tệp tin không tồn tại
            return response()->json(['message' => 'File not found'], 404);
        }
    }
}
