<?php
namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Image;
use File;

class StoreImage {
    public static function saveImage($request, $name=null, $slug, $form_name)
    {
    	$file       = $request->file($form_name);
        $ext        = $file->getClientOriginalExtension();
	    $filename   = strtolower(str_replace(array(' ', '&', '.'), array('-', '', ''), $slug)). '.' . $ext;
        $image_resize = Image::make($file->getRealPath());

            if(is_null($name)){
                $path = 'images/';
            }else{
                $path = 'images/'.$name;
            }

	    if(!File::exists($path)) {
	        File::makeDirectory($path, 0755, true);
	    }
	    $file->move($path, $filename);
	    return $path.'/'.$filename;
    }

    public static function deleteImage($filename) {
        File::delete($filename);
    }
}
