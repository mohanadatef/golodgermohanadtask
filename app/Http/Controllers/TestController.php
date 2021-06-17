<?php
    
    namespace App\Http\Controllers;
    
    use Illuminate\Support\Facades\Http;
    
    class TestController extends Controller
    {
        public function index()
        {
            //get api in variable
            $response_1=Http::get('https://f704cb9e-bf27-440c-a927-4c8e57e3bad1.mock.pstmn.io/s1/availability')->json();
            $response_2=Http::get('https://f704cb9e-bf27-440c-a927-4c8e57e3bad1.mock.pstmn.io/s2/availability')->json();
            $rooms=array();
            //get api in array
            $response[]=$response_1;
            $response[]=$response_2;
            foreach($response as $data)
            {
                foreach($data['hotels'] as $hotel)
                {
                    foreach($hotel['rooms'] as $room)
                    {
                        //get all code duplicate
                        $code=array_column($rooms,'code');
                        //check if duplicate
                        if($code)
                        {
                            if(in_array($room['code'],$code))
                            {
                                //get key duplicate
                                $key=array_search($room['code'],$code);
                                $total=isset($room['total'])?$room['total']:(isset($room['totalPrice'])?$room['totalPrice']:'0');
                                //check price
                                if($rooms[$key]['total']<$total)
                                {
                                    $rooms[$key]=$this->room($hotel,$room);//set array
                                }else
                                {
                                    continue;
                                }
                            }else
                            {
                                $rooms[]=$this->room($hotel,$room);//set array
                            }
                        }else
                        {
                            $rooms[]=$this->room($hotel,$room);//set array
                        }
                    }
                }
            }
            //sort by price
            $column_sort = array_column($rooms, 'total');
            array_multisort($column_sort, SORT_ASC, SORT_REGULAR, $rooms);
            //value
            return response(['room'=>$rooms],200);
        }
        //set array
        public function room($hotel,$room)
        {
            $taxes=array(gettype($room['taxes'])=='array'?isset($room['taxes'][0]['amount'])?$room['taxes'][0]['amount']:(isset($room['taxes']['amount'])?$room['taxes']['amount']:''):"",
                         gettype($room['taxes'])=='array'?isset($room['taxes'][0]['currency'])?$room['taxes'][0]['currency']:(isset($room['taxes']['currency'])?$room['taxes']['currency']:""):"",
                         gettype($room['taxes'])=='array'?isset($room['taxes'][0]['type'])?$room['taxes'][0]['type']:(isset($room['taxes']['type'])?$room['taxes']['type']:""):"");
            return ['code'       =>isset($room['code'])?$room['code']:"",'name'=>isset($room['name'])?$room['name']:"",
                    'price'      =>isset($room['net_rate'])?$room['net_rate']:(isset($room['net_price'])?$room['net_price']:'0'),
                    'total'      =>isset($room['total'])?$room['total']:(isset($room['totalPrice'])?$room['totalPrice']:'0'),
                    'hotel_name' =>isset($hotel['name'])?$hotel['name']:"",
                    'hotel_stars'=>isset($hotel['stars'])?$hotel['stars']:"",'taxes'=>$taxes];
        }
    }
