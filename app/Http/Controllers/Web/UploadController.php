<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Uploads;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    //
    public function viewImage(Uploads $uploads)
    {
        $data = $uploads->getUpload();
        return view('viewImage')->with([
            'data'=>$data
        ]);
    }

    public function uploadImage(Request $request, Uploads $uploads)
    {
        $uploads->uploadImg($request);
        return back();
    }
}
