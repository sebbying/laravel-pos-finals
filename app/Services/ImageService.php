<?php
namespace App\Services;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
class ImageService {
 public function store(UploadedFile $file): string {
  $size = @getimagesize($file->getRealPath());
  if (!$size || $size[0]*$size[1] > 16000000) throw ValidationException::withMessages(['upload'=>'Image must contain at most 16 million pixels.']);
  $source = @imagecreatefromstring(file_get_contents($file->getRealPath()));
  if (!$source) throw ValidationException::withMessages(['upload'=>'The image could not be decoded.']);
  $ratio = min(1, 1000/max($size[0],$size[1]));
  $dest = imagecreatetruecolor(max(1,(int)($size[0]*$ratio)),max(1,(int)($size[1]*$ratio)));
  imagefill($dest,0,0,imagecolorallocate($dest,255,255,255));
  imagecopyresampled($dest,$source,0,0,0,0,imagesx($dest),imagesy($dest),$size[0],$size[1]);
  ob_start(); imagejpeg($dest,null,85); $bytes=ob_get_clean();
  imagedestroy($source); imagedestroy($dest);
  $path='images/'.Str::uuid().'.jpg'; Storage::put($path,$bytes); return $path;
 }
}
