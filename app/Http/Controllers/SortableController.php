<?php
    
namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use App\Models\Cuisine;
    
class SortableController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateOrder(Request $request,$model)
    {
            // $model='Cuisine';
        $model = app("App\Models\\$model");
        foreach ($request->order as $key => $value) {

            if(isset(explode('row-',$value)[1])){
                $id=explode('row-',$value)[1];
                if($id > 0){
                    $model->find($id)->update(['sort_order'=>$key]);
                }
            }
        }
      
        return 1;
    }
    
  
}