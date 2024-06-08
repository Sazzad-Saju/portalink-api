<?php
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

if (! function_exists('makeOriginals')) {
    function makeOriginals($url)
    {
        $ext = pathinfo($url)['extension'];

        $today = date('Y-m-d');
        $originalName = "items/$today/original/" . Str::uuid() . ".$ext";

        if(@getimagesize($url)){
            $inputImage = file_get_contents($url);
        }else{
            $inputImage = asset('img/no-img.jpg');
        }

        Storage::put($originalName, $inputImage);
        return $originalName;
    }
}

if (! function_exists('makeThumbs')) {
    function makeThumbs($file)
    {
        $today = date('Y-m-d');
        $thumbs = Image::make($file)->resize(100, 150)->encode('jpg', 100);
        $thumbsName = "items/$today/thumbs/" . Str::uuid() . '.jpg';
        Storage::put($thumbsName, $thumbs);
        return $thumbsName;
    }
}
if (! function_exists('makeCompressed')) {
    function makeCompressed($file)
    {
        $today = date('Y-m-d');
        $compressed = Image::make($file)->resize(600, 900)->encode('webp', 80);
        $compressedName = "items/$today/compressed/" . Str::uuid() . '.webp';
        Storage::put($compressedName, $compressed);
        return $compressedName;
    }
}
if (! function_exists('makeMobileImage')) {
    function makeMobileImage($file)
    {
        $today = date('Y-m-d');
        $compressed = Image::make($file)->resize(300, 450)->encode('webp', 80);
        $compressedName = "items/$today/mobile/" . Str::uuid() . '.webp';
        Storage::put($compressedName, $compressed);
        return $compressedName;
    }
}
if (! function_exists('makeCompressedSf')) {
    function makeCompressedSf($file)
    {
        $today = date('Y-m-d');
        $comsf = Image::make($file)->resize(600, 900)->encode('jpg', 80);
        $comsfName = "items/$today/comsf/" . Str::uuid() . '.jpg';
        Storage::put($comsfName, $comsf);
        return $comsfName;
    }
}

if (! function_exists('copyOriginals')) {
    function copyOriginals($file)
    {
        $today = date('Y-m-d');
        $originalName = "items/$today/original/" . Str::uuid() . '.jpg';
        Storage::copy($file, $originalName);
        return $originalName;
    }
}

if (! function_exists('saveImageFromUrl')) {
    function saveImageFromUrl($url, $store_path, $height = null, $width = null)
    {
        if (@getimagesize($url)) {
            if($height && $width){
                $images = Image::make($url)->resize($height, $width)->encode('jpg');
                $imageName = $store_path . Str::uuid() . '.jpg';
                Storage::put($imageName, $images);
                return $imageName;
            }else{
                $filenameArray = explode('/',$url);
                $path = $store_path .$filenameArray[count($filenameArray) - 1];
                Storage::disk('public')->put($path, file_get_contents($url));
                return $path;
            }
        }

        return null;
    }
}

if (! function_exists('imageResize')) {
    function imageResize($file, $store_path = '',$width = null, $height = null)
    {
        $resizeImage = Image::make($file)->resize($width, $height, function ($constraint) {
            $constraint->aspectRatio();
        })->encode('jpg', 100);

        $resizeImageName = $store_path . '/' . Str::uuid() . '.jpg';

        Storage::put($resizeImageName, $resizeImage);

        return $resizeImageName;
    }
}

if (! function_exists('temporaryImageUploadFromBase64')) {
    function temporaryImageUploadFromBase64($url)
    {
        $inputImage = Image::make($url);
        $today = date('Y-m-d');
        $storeImageName = $today . '.webp';
        $storedImage = "text-editor/$today/" . Str::uuid() . '.webp';

        Storage::put($storedImage, (string) $inputImage->encode('webp', 70));
        return $storedImage;
    }
}

if (! function_exists('generateCustomerUid')) {
    function generateCustomerUid()
    {
        $number = random_int(100000, 999999);
        for($i = 0; $i <= 10; $i++){
            $count = \App\Models\Customer::where('customer_uid', $number)->count();
            if($count == 0)
                return $number;
            else
                $number = random_int(100000, 999999);
        }
    }
}
