<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use Illuminate\Http\Request;

class DeliveryBoyManager extends Controller
{
    function getDelivery(Request $request){
       $delivery = Orders::where("delivery_boy_email", $request->email)-> where("status", "assigned")-> orderBy("id", "desc")-> get();

       return $delivery;
    }

    function markStatus(Request $request){
        $order = Orders::where("id", $request->id)-> first();
        $order->status = $request->status;
        if($order->save()){
            return "success";
        }else{
            return "failed";
        }
    }

    function markStatusSuccess(Request $request){
   
        return $this->markStatus($request,"success"); 

    }
    function markStatusFailed(Request $request){    
        return $this->markStatus($request,"success"); 
    }
}
