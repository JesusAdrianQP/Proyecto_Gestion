<?php

namespace App\DataBase;

use App\Service;
use App\Price;
use App\Favorite;
use App\DataBase\UserDataMaster;
use App\Http\Controllers\MailController;

class ServiceData
{ 
    //Función que lista todos los servicios
    public static function service_list($new_page)
    {
        $pmin = (int) $new_page->pmin;
        $pmax = (int) $new_page->pmax;

        $services = Service::title($new_page->title)
                    ->localizacion($new_page->district)
                    ->category($new_page->category)
                    ->pmin($pmin)
                    ->pmax($pmax)
                    ->calificacion($new_page->value)
                    ->paginate(6, ['*'], 'services', $new_page->page);
        
        return response()->json([
            'paginate' => $services
        ]);
    }
    
    public static function listiddetails($id){
        $services_id = Service::where('_id', '=', $id->service_id)->get();

        return response()->json([
            'serv_details' => $services_id
        ]);
    }

    //Funcion que trae los detalles de la estimacion de precios del servicio
    public static function pridetails($id)
    {
        $exist = Price::where('id_service', '=', $id->service_id)->first();

        if($exist){
            return response()->json([
                'pri_details' => $exist
            ], 200);
        }
        else return response()->json(['errors' => ['fail' => ['Cotización no encontrada!']]], 422);
    }

    public static function register($new_service)
    {
        //Verifico que mi usuario no haya publicado más de 3 anuncios
        if(Service::where('token', '=', $new_service->id)->count()<3){
            //Se crea una nueva colección
            $service = new Service();

            //Guardo los datos en mi colección
            $service->identity = $new_service->level;
            $service->token = $new_service->id; /*Codigo del proveedor*/
            $service->title = $new_service->title;
            $service->description = $new_service->description;
            $service->category = $new_service->category;
            $service->distrito = $new_service->district;
            $service->calificacion = 0;
            $service->precio = (float) $new_service->price;
            $service->file = $new_service->photo;

            $service->save();

            ServiceData::register_price($service->_id, $new_service->price, $new_service->materials);

            return response()->json([
                'success' => 'Su servicio ha sido creado',
                //'pmin' => $service->precioMin,
                //'pmax' => $service->precioMax
            ], 200);
        }
        else return response()->json(['errors' => ['fail' => ['No se puede tener más de 3 servicios activos']]], 422);
    }

    public static function register_price($id_service, $pricemdo, $mat){
        $price = new Price();

        $price->price_mdo = $pricemdo;
        $price->materials = $mat;
        $price->id_service = $id_service;

        $price->save();
    }

    //Funcion de actualizacion de datos de servicio
    public static function update_service($new_service)
    {
        //Encuentro mis datos de servicio
        $service = Service::where('_id', '=', $new_service->id_service)->first();
 
        // Si existe
        if($service){
            //Seteamos
            $service->title = $new_service->title;
            $service->description = $new_service->description;
            $service->category = $new_service->category;
            $service->departamento = $new_service->department;
            $service->provincia = $new_service->province;
            $service->distrito= $new_service->district;
            $service->precioMin= $new_service->min_price;
            $service->precioMax= $new_service->max_price;
            $service->file= $new_service->photo;

            // Guardamos en base de datos
            $service->save();

            return response()->json(['success' => ['Cambios guardados']], 200);
        }else return response()->json(['errors' => ['fail' => ['Hubo un error de conexión ... Intente más tarde']]], 422);
    }

    //Funcion de actualizacion de cotización de servicio
    public static function update_price($new_price)
    {
        //Encuentro mi cotizacion de servicio
        $price = Price::where('id_service', '=', $new_price->id_service)->first();
 
        //Actualizamos el precio maximo solo si es necesario
        if($new_price->pmax != 0)
        {
            $service = Service::find($new_price->id_service); //Encontramos el servicio
            $service->precioMax = (int) $new_price->pmax; //Actualizamos
            $service->save(); //Guardamos
        } 

        // Si existe
        if($price){
            //Seteamos
            $price->price_mdo = $new_price->laboral;
            $price->materials = $new_price->materials;
            $price->id_service = $new_price->id_service;

            // Guardamos en base de datos
            $price->save();

            return response()->json(['success' => ['Cambios guardados']], 200);
        }else return response()->json(['errors' => ['fail' => ['Hubo un error de conexión ... Intente más tarde']]], 422);
    }

    public static function save_service($service)
    {
        $favorite = new Favorite();

        $favorite->idService = $service->idService;
        $favorite->idCustomer = $service->idCustomer;

        $favorite->save();
    }

    public static function favoriteBoolean($favorite)
    {
        $favorite = Favorite::where('idService', '=', $favorite->idService)
                              ->where('idCustomer', '=', $favorite->idCustomer)
                              ->first();
        
        if($favorite) return 1;
        else return 0;
    }
}
