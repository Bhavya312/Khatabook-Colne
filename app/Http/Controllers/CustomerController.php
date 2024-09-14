<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCustomerRequest;
use App\Http\Requests\EditCustomerRequest;
use App\Models\Customer;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try{
            $limit = $request->limit ?? 10;
            $sortColumn = $request->sort_column ?? 'created_at';
            $sortOrder = $request->sort_order ?? 'desc';
            $search = trim($request->search);

            $customers = Customer::with('user');

            if($search):
                $customers = $customers->where(function($query) use($search){
                                            $query->where('user_name', 'LIKE', '%'.$search.'%');
                                            $query->orWhere('shop_name', 'LIKE', '%'.$search.'%');
                                            $query->orWhere('phone_number', 'LIKE'. '%'.$search.'%');
                });
            endif;

            $customers = $customers->orderBy($sortColumn, $sortOrder);
            $customers = $customers->latest()->paginate($limit);
            return response()->json([
                                            'status' => 200,
                                            'data' => $customers,
                                        ], 200);
        }catch(Exception $e) {
            return response()->json([
                                        'status' => 500,
                                        'error' => __('notifications.something_went_wrong')
                                    ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            $addCustomerRequest = new AddCustomerRequest();
            $validator = Validator::make($request->all(), $addCustomerRequest->rules());
            if($validator->fails()):
                return response()->json([
                                    'status' => 403,
                                    'error' => $validator->errors(),
                                ], 403);
            else:
                DB::beginTransaction();
                $customer = new Customer();
                $customer->user_id = $request->user_id;
                $customer->user_name = $request->user_name;
                $customer->shop_name = $request->shop_name;
                $customer->phone_number = $request->phone_number;
                $customer->save();
                DB::commit();
                return response()->json([
                                            'status' => 200,
                                            'error' => __('notifications.data_created', ['model' => "Customer"])
                                        ], 200);
            endif;
        }catch(Exception $e){
            DB::rollBack();
            return response()->json([
                                        'status' => 500,
                                        'error' => __('notifications.something_went_wrong')
                                    ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try{
            $customer = Customer::find($id);
            if($customer):
                return response()->json([
                                            'status' => 200,
                                            'data' => $customer,
                                            'message' => __('notifications.data_found', ['model' => "Customer"])
                                        ], 200);
            else:
                return response()->json([
                                            'status' => 200,
                                            'error' => __('notifications.data_not_found', ['model' => "Customer"])
                                        ], 200);
            endif;
        }catch(Exception $e){
            return response()->json([
                                        'status' => 500,
                                        'error' => __('notifications.something_went_wrong')
                                    ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try{
            $customer = Customer::find($id);
            if($customer):
                $editCustomerRequest = new EditCustomerRequest();
                $validator = Validator::make($request->all(), $editCustomerRequest->rules());
                if($validator->fails()):
                    return response()->json([
                                            'status' => 403,
                                            'error' => $validator->errors(),
                                        ], 403);
                else:
                    DB::beginTransaction();
                        $customer->user_name = $request->user_name;
                        $customer->shop_name = $request->shop_name;
                        $customer->city = $request->city;
                        $customer->phone_number = $request->phone_number;
                        $customer->update();
                    DB::commit();
                endif;

                return response()->json([
                                            'status' => 200,
                                            'data' => $customer,
                                            'message' => __('notifications.data_updated', ['model' => "Customer"])
                                        ], 200);
            else:
                return response()->json([
                                            'status' => 200,
                                            'error' => __('notifications.data_not_found', ['model' => "Customer"])
                                        ], 200);
            endif;
        }catch(Exception $e){
            DB::rollBack();
            return response()->json([
                                        'status' => 500,
                                        'error' => __('notifications.something_went_wrong')
                                    ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try{
            $customer = Customer::find($id);
            if($customer):
                DB::beginTransaction();
                    $customer->delete();
                DB::commit();
                return response()->json([
                                            'status' => 200,
                                            'message' => __('notifications.data_deleted', ['model' => "Customer"])
                                        ]);
            else:
                return response()->json([
                                            'status' => 200,
                                            'error' => __('notifications.data_not_found', ['model' => "Customer"])
                                        ], 200);
            endif;
        }catch(Exception $e){
            DB::rollBack();
            return response()->json([
                                        'status' => 500,
                                        'error' => __('notifications.something_went_wrong')
                                    ], 500);
        }
    }
}
