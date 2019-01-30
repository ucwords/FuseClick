<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;
use App\Models\HandleOffer;

class HandController extends Controller
{

    public function __construct(){
        $this->middleware('auth');
    }


    public function getOffer()
    {
        $data = HandleOffer::orderBy('create_time', 'desc')->get();

        $reponse_data = [
          'code'  => 0,
          'message' => 'success',
          'offer_count'  => count($data),
          'offers'  => $data,
        ];
        return response()->json($reponse_data);
    }

    public function testRedis()
    {
        if (Redis::exists('name')) {
            dd('ok');
        }
    }

    public function authBasic()
    {

    }
}
