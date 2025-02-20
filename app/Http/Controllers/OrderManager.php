<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Orders;
use App\Models\Products;
use App\Models\User;
use DB;
use Http;
use Illuminate\Http\Request;

class OrderManager extends Controller
{
    function newOrders(){
        $orders = Orders::where("status","open")->get();
        $orders = json_decode(json_encode($orders));
        $delivery_boys = User::where("type","delivery")->get();
        $products = Products::get();

        foreach ($orders as $key => $order){
            $order_item_ids = json_decode($order->items);
            foreach ($order_item_ids as $key2 => $order_item){
                foreach ($products as $product){
                    if($order_item->item_id == $product->id){
                       $orders[$key]->item_details[$key2]->name = $product;
                    }
                }
            }

        }
        return view('dashboard',compact("orders","delivery_boys"));
    }

    function assignOrder(Request $request){
        $order = Orders::where("id",$request->order_id)->first();
        $order->delivery_boy_email = $request->delivery_boy_email;
        $order->status = "assigned";
        if($order->save()){
            return redirect()->route('dashboard')->with('success','Order assigned successfully');
        } else{
            return back()->with('error','Order assign failed');
        }

    }

    function listOrders(){
        $orders = Orders::orderBy('id','desc')->get();
        $orders = json_decode(json_encode($orders));
        $products = Products::get();

        foreach ($orders as $key => $order){
            $order_item_ids = json_decode($order->items);
            foreach ($order_item_ids as $key2 => $order_item){
                foreach ($products as $product){
                    if($order_item->item_id == $product->id){
                       $orders[$key]->item_details[$key2]->name = $product;
                    }
                }
            }

        }
        return view('order',compact("orders"));
    }

    function addToCart(Request $request){
        $cart = new Cart();
        $cart->item_id = $request->item_id;
        $cart->user_email = $request->user_email;
        if($cart->save()){
            return "success";
        }

        return "failed";
    }

    function removeFromCart(Request $request){
        $cart = Cart::where("item_id",$request->item_id)->where("user_email",$request->user_email)->first();

        if($cart == null){
            return "failed";
        }
        if($cart->delete()){
            return "success";
        }

        return "failed";
    }

    function getCart(Request $request){
        $item_id = array();
        $count_items = DB::select("SELECT item_id, COUNT(item_id) as num_item from cart where user_email = '".$request->user_email."' GROUP BY item_id");
        foreach ($count_items as $key => $item){
           $item_id[$key] = $item->item_id;
        }

        $user = User::where("email",$request->user_email)->first();
        $dist_dur = $this->calculateEstimatedTime($user);
        $products = Products::whereIn('id',$item_id)->get();
        foreach($count_items as $item){
            foreach($products as  $key => $product){
                if($item->item_id == $product->id){
                    $products[$key]->num_item = $item->num_item;
                }
            }

        }
        $data = array();
        $data['products'] = $products;
        array_push($data,array("cart"=>json_decode(($products)),"duration"=>$dist_dur['duration'],"distance"=>$dist_dur['distance']));

        return $data;
    }

    function confirmOrder(Request $request){
        $cart = Cart::select('item_id')->where("user_email",$request->user_email)->get();
        if($cart->isEmpty()){
            return "failed";
        }

        $user = User::where("email",$request->user_email)->first();
        $order = new Orders();
        $order->customer_email = $request->user_email;
        $order->items = $cart;
        $order->status = "open";
        $order->destination_address = $user->destination_address;
        $order->destination_lat = $user->destination_lat;
        $order->destination_lon = $user->destination_lon;
        if($order->save()){
           if($this->clearCart($request) == "success"){
               return "success";
        }else{
            return "failed";
        }
    }
}



    function clearCart(Request $request){
        $cart = Cart::where("user_email",$request->user_email)->delete();
        if($cart){
            return "success";
        }
        return "failed";
    }

    function getOrders(Request $request){
        $orders = Orders::where("customer_email",$request->email)->orderBy('id','desc')->get();
        $orders = json_decode(json_encode($orders));
        $products = Products::get();

        foreach ($orders as $key => $order){
            $order_item_ids = json_decode($order->items);
            foreach ($order_item_ids as $key2 => $order_item){
                foreach ($products as $product){
                    if($order_item->item_id == $product->id){
                       $orders[$key]->item_details[$key2] = $product;
                    }
                }
            }

        }
        return $orders;
    }

    function calculateEstimatedTime($user){
       //https://developer.mapquest.com/account/user/me/apps
       //hzxSrjjrrPVL83pddKhNGyf3JWJDAA81
       //https://www.mapquestapi.com/directions/v2/route?key=YOUR_API_KEY&from=LAT1,LON1&to=LAT2,LON2
       //from = Qasr mall sultanah, Riyadh 12241, Saudi Arabia
       //to = salam mall sultanah, Riyadh 12241, Saudi Arabia
        
       $apiurl = "https://www.mapquestapi.com/directions/v2/route?key=hzxSrjjrrPVL83pddKhNGyf3JWJDAA81&from=24.600577848196522,46.697443506568035&to=$user->destination_lat,$user->destination_lon";
       $response = json_decode(Http::get($apiurl));

       $dist_dur['distance'] = $response['route']['distance'];  // Distance in kilometers
       $dist_dur['duration'] = $response['route']['formattedTime'];  // Duration as formatted time (hh:mm:ss)   

         return $dist_dur;
    }
}

