<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BalanceController extends Controller
{
    private function getFormatedBalanceList($userBalance){
        $balanceList = array();
        foreach ($userBalance as $balance) {
            $balanceItem = array(
                "category" => $balance->category->name,
                "category_id" => $balance->category->id,
                "ammount" => $balance->ammount,
                "total" => $balance->total,
                "type" => $balance->type,
                "description" => $balance->description,
            );
            array_push($balanceList, $balanceItem);
        }
        return $balanceList;
    }

    public function getBalance(Request $request) {
        $page_count = $request->query('perPages',10);
        $category = $request->query('category',null);
        $total_from = $request->query('from',null);
        $total_to = $request->query('to',null);
        $type = $request->query('type',null);
        $user = $request->user();
        $balance = Balance::where('user_id', $user->id)->orderBy('id','desc');

        if(!is_null($category)) {
            $balance = $balance->where('category_id', $category);
        }

        if(!is_null($type)) {
            $balance = $balance->where('type', $type);
        }

        if(!is_null($total_from)) {
            $balance = $balance->where('total','>=', $total_from);
        }

        if(!is_null($total_to)) {
            $balance = $balance->where('total','<=', $total_to);
        }

        $balance = $balance->paginate($page_count);
        $total_records = $balance->total();
        $current_page = $balance->currentPage();
        $last_page = $balance->lastPage();
        $per_page = $balance->perPage();
        $balance->load('category');
        $data = $balance->items();


        $lastBalance = Balance::where('user_id', $user->id)->orderBy('id', 'desc')->first();
        $currentTotal = $balance->isNotEmpty() ?  $lastBalance->total : 0;

        $balanceResponse = array(
            "currentAmmount" => $currentTotal,
            "balanceHistory" => $data,
            "totalRecords" => $total_records,
            "lastPage" => $last_page,
            "currentPage" => $current_page,
            "perPage" => $per_page,
        );
        return $this->successGet($balanceResponse);
    }

    public function insertActive(Request $request) {
        $user = $request->user();
        $requestData = $request->only('type','ammount','description','category');
        if(empty($requestData)) return $this->badRequest('Debe ser enviada informacion');

        $validacion = Validator::make($requestData, [
            'category' => 'required',
            'ammount' => 'required|numeric'
        ]);

        if ($validacion->fails()) return $this->badRequest('Revise los datos enviados', $validacion->errors());

        if($requestData['ammount'] < 0) return $this->badRequest('El monto tiene que ser positivo');

        $currentBalance = $user->balance;

        $balance = new Balance();
        $balance->user_id = $user->id;
        $balance->type = 'in';
        $balance->category_id = $requestData['category'];
        $balance->ammount = $requestData['ammount'];
        $balance->description = $requestData['description'];
        if ($currentBalance->isNotEmpty()) {
            $balance->total = $currentBalance->last()->total + $requestData['ammount'];
        }else {
            $balance->total = $requestData['ammount'];
        }

        if (!$balance->save()) return $this->internalError('Ocurrio un error');

        // $updatedBalance = $this->getFormatedBalanceList(Balance::where('user_id', $user->id)->get());
        $updatedBalance = Balance::where('user_id', $user->id)->orderBy('id', 'desc')->first();
        $updatedBalance->load('category');

        return $this->successCreation('Ingreso registrado', $updatedBalance);
    }

    public function insertPassive(Request $request) {
        $user = $request->user();
        $requestData = $request->only('type','ammount','description','category');
        if(empty($requestData)) return $this->badRequest('Debe ser enviada informacion');

        $validacion = Validator::make($requestData, [
            'category' => 'required',
            'ammount' => 'required|numeric'
        ]);

        if ($validacion->fails()) return $this->badRequest('Revise los datos enviados', $validacion->errors());

        if($requestData['ammount'] < 0) return $this->badRequest('El monto tiene que ser positivo');

        $currentBalance = $user->balance;

        $balance = new Balance();
        $balance->user_id = $user->id;
        $balance->type = 'out';
        $balance->category_id = $requestData['category'];
        $balance->ammount = $requestData['ammount'];
        $balance->description = $requestData['description'];
        if ($currentBalance->isNotEmpty()) {
            $balance->total = $currentBalance->last()->total - $requestData['ammount'];
        }else {
            $balance->total = $requestData['ammount'];
        }

        if (!$balance->save()) return $this->internalError('Ocurrio un error');

        // $updatedBalance = $this->getFormatedBalanceList(Balance::where('user_id', $user->id)->get());
        $updatedBalance = Balance::where('user_id', $user->id)->orderBy('id', 'desc')->first();
        $updatedBalance->load('category');

        return $this->successCreation('Ingreso registrado', $updatedBalance);
    }
}
