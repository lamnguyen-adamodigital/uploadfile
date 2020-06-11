<?php

namespace App\Models;

use App\Libraries\Ultilities;
use Illuminate\Database\Eloquent\Model;

class Uploads extends Model
{
    //
    protected $table = 'uploads';
    protected $fillable = ['id', 'name', 'image'];
    public function getUpload()
    {
        return $this->get();
    }
    public function uploadImg($request)
    {
        if($request->hasFile('album')){
            $nameImage= Ultilities::uploadFile($request->file('album'));
            $data = [
                'name'=>'adamo devops',
                'image'=>$nameImage
            ];
            return $this->create($data);
        }
    }
}
