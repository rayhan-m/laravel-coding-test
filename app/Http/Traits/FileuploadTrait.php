<?php

namespace App\Http\Traits;

use Symfony\Component\HttpFoundation\File\UploadedFile;


trait FileuploadTrait
{

    public function uploadFile($request)
    {
        $folderPath = public_path() . '/';
        $image_parts = explode(";base64,", $request->product_image);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);
        $fileName =  "img/product-".time() . '.' . $image_type;
        $file = $folderPath . $fileName;
        file_put_contents($file, $image_base64);
        return $fileName;
    }
}
