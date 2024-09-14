<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddTransactionRequest;
use App\Http\Requests\EditTransactionRequest;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
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

            $transactions = Transaction::with('customers');

            if($search):
                $transactions = $transactions->where(function($query) use($search){
                                            $query->where('amount', 'LIKE', '%'.$search.'%');
                                            $query->orWhere('transaction_type', 'LIKE', '%'.$search.'%');
                                            $query->orWhere('description', 'LIKE', '%'.$search.'%');

                                            $query->orWhereHas('customers', function($query) use($search) {
                                                $query->where('username', 'LIKE', '%'.$search.'%');
                                                $query->orWhere('shop_name', 'LIKE', '%'.$search.'%');
                                                $query->orWhere('city', 'LIKE', '%'.$search.'%');
                                            });
                                    });
            endif;

            $transactions = $transactions->orderBy($sortColumn, $sortOrder);
            $transactions = $transactions->latest()->paginate($limit);
            return response()->json([
                                            'status' => 200,
                                            'data' => $transactions,
                                        ], 200);
        }catch(Exception $e) {
            return $e;
            return response()->json([
                                        'status' => 500,
                                        'error' => __('notifications.somthing_went_wrong')
                                    ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            $addTransactionRequest = new AddTransactionRequest();
            $validator = Validator::make($request->all(), $addTransactionRequest->rules());
            if($validator->fails()):
                return response()->json([
                                    'status' => 403,
                                    'error' => $validator->errors(),
                                ], 403);
            else:
                DB::beginTransaction();
                    $transaction = new Transaction();
                    $transaction->customer_id = $request->customer_id;
                    $transaction->transaction_type = $request->transaction_type;
                    $transaction->amount = $request->amount;
                    $transaction->transaction_date = Carbon::parse($request->transaction_date)->format('Y-m-d');
                    $transaction->description = $request->description;
                    $transaction->save();
                DB::commit();
                return response()->json([
                                            'status' => 200,
                                            'error' => __('notifications.data_created', ['model' => "Transaction"])
                                        ], 200);
            endif;
        }catch(Exception $e){
            DB::rollBack();
            return response()->json([
                                        'status' => 500,
                                        'error' => __('notifications.somthing_went_wrong')
                                    ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try{
            $transaction = Transaction::find($id);
            if($transaction):
                return response()->json([
                                            'status' => 200,
                                            'data' => $transaction,
                                            'message' => __('notifications.data_found', ['model' => "Transaction"])
                                        ], 200);
            else:
                return response()->json([
                                            'status' => 200,
                                            'error' => __('notifications.data_not_found', ['model' => "Transaction"])
                                        ], 200);
            endif;
        }catch(Exception $e){
            return response()->json([
                                        'status' => 500,
                                        'error' => __('notifications.somthing_went_wrong')
                                    ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try{
            $transaction = Transaction::find($id);
            if($transaction):
                $editTransactionRequest = new EditTransactionRequest();
                $validator = Validator::make($request->all(), $editTransactionRequest->rules());
                if($validator->fails()):
                    return response()->json([
                                            'status' => 403,
                                            'error' => $validator->errors(),
                                        ], 403);
                else:
                    DB::beginTransaction();
                        $transaction->transaction_type = $request->transaction_type;
                        $transaction->amount = $request->amount;
                        $transaction->transaction_date = Carbon::parse($request->transaction_date)->format('Y-m-d');
                        $transaction->description = $request->description;
                        $transaction->update();
                    DB::commit();
                endif;

                return response()->json([
                                            'status' => 200,
                                            'data' => $transaction,
                                            'message' => __('notifications.data_updated', ['model' => "Transaction"])
                                        ], 200);
            else:
                return response()->json([
                                            'status' => 200,
                                            'error' => __('notifications.data_not_found', ['model' => "Transaction"])
                                        ], 200);
            endif;
        }catch(Exception $e){
            DB::rollBack();
            return response()->json([
                                        'status' => 500,
                                        'error' => __('notifications.somthing_went_wrong')
                                    ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try{
            $transaction = Transaction::find($id);
            if($transaction):
                DB::beginTransaction();
                    $transaction->delete();
                DB::commit();
                return response()->json([
                                            'status' => 200,
                                            'message' => __('notifications.data_deleted', ['model' => "Transaction"])
                                        ]);
            else:
                return response()->json([
                                            'status' => 200,
                                            'error' => __('notifications.data_not_found', ['model' => "Transaction"])
                                        ], 200);
            endif;
        }catch(Exception $e){
            DB::rollBack();
            return response()->json([
                                        'status' => 500,
                                        'error' => __('notifications.somthing_went_wrong')
                                    ], 500);
        }
    }
}
