<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateRequest;
use Illuminate\Http\Request;
use App\DataBase\ServiceData;

class ServiceController extends Controller
{
    //Función que llama a todos los servicios paginados
    public function serviceall(Request $request)
    {
        return ServiceData::service_list($request);
    }
}
