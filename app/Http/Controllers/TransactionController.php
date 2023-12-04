<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    private function formatTransactions($transactions){
        $formatedList = array();
        foreach ($transactions as $item) {
            $from = User::find($item->from);
            $to = User::find($item->to);
            $trx = array(
                "id" => $item->id,
                "from_id" => $from->id,
                "from" => $from->name . ' ' . $from->lastName,
                "to_id" => $to->id,
                "to" => $to->name . ' ' . $to->lastName,
                "ammount" => $item->ammount,
                "created_at" => $item->created_at
            );
            array_push($formatedList, $trx);
        }
        return $formatedList;
    }

    public function getTransactions(Request $request) {
        $page_count = $request->query('perPages',10);
        $to = $request->query('to',null);
        $ammount_from = $request->query('ammount_from',null);
        $ammount_to = $request->query('ammount_to',null);
        $user = $request->user();
        $transactions = Transaction::where('from', $user->id)->orderBy('id','desc');

        if(!is_null($to)) {
            $transactions = $transactions->where('to', $to);
        }

        if(!is_null($ammount_from)) {
            $transactions = $transactions->where('ammount','>=',$ammount_from);
        }

        if(!is_null($ammount_to)) {
            $transactions = $transactions->where('ammount','<=',$ammount_to);
        }

        $transactions = $transactions->paginate($page_count);
        $total_records = $transactions->total();
        $current_page = $transactions->currentPage();
        $last_page = $transactions->lastPage();
        $per_page = $transactions->perPage();

        $transactionsFormated = $this->formatTransactions($transactions->items());

        $transactionsResponse = array(
            "transactionHistory" => $transactionsFormated,
            "totalRecords" => $total_records,
            "lastPage" => $last_page,
            "currentPage" => $current_page,
            "perPage" => $per_page,
        );
        return $this->successGet($transactionsResponse);
    }

    public function makeTransaction(Request $request){
        $user = $request->user();
        $requestData = $request->only('ammount','cvu','alias','detail');
        $transactionMethod = 'cvu';
        if (is_null($requestData['cvu'])) {
            if (is_null($requestData['alias'])) {
                return $this->badRequest('Se debe indicar al menos un dato del usuario a transferir');
            }
            $transactionMethod = 'alias';
            if ($requestData['alias'] == $user->alias) return $this->badRequest('No se pueden realizar transferencias desde y hacia la misma cuenta');
        }

        if ($transactionMethod == 'cvu') {
            if ($requestData['cvu'] == $user->cvu) return $this->badRequest('No se pueden realizar transferencias desde y hacia la misma cuenta');
        }

        $userBalance = $user->balance;
        if($userBalance->isEmpty()) return $this->badRequest('No tiene saldo suficiente para realizar la transaccion');
        $lastBalance = Balance::where('user_id', $user->id)->orderBy('id', 'desc')->first();
        if($lastBalance->total < $requestData['ammount']) return $this->badRequest('No tiene saldo suficiente para realizar la transaccion');

        $userToTransfer = $transactionMethod == 'alias' ? User::where('alias',$requestData['alias'])->first() : User::where('cvu',$requestData['cvu'])->first();
        if(is_null($userToTransfer)) return $this->badRequest('El usuario al que desea transferir no existe');
        if($userToTransfer->id == $user->id) return $this->badRequest('Imposible transferir desde y hacia la misma cuenta');

        $newTransaction = new Transaction();
        $newTransaction->from = $user->id;
        $newTransaction->to = $userToTransfer->id;
        $newTransaction->ammount = $requestData['ammount'];
        // $newTransaction->detail = boolval($requestData['detail']) ? $requestData['detail'] : null;
        if (!$newTransaction->save()) return $this->internalError('Ocurrio un error al intentar realizar la transaccion');

        $balanceOut = new Balance();
        $balanceOut->user_id = $user->id;
        $balanceOut->type = 'out';
        $balanceOut->category_id = 2;
        $balanceOut->ammount = $requestData['ammount'];
        // $balanceOut->description = boolval($requestData['detail']) ? $requestData['detail'] : null;
        $balanceOut->total = $userBalance->last()->total - $requestData['ammount'];

        if (!$balanceOut->save()) return $this->internalError('Ocurrio un error');

        $balanceIn = new Balance();

        $balanceIn->user_id = $userToTransfer->id;
        $balanceIn->type = 'in';
        $balanceIn->category_id = 2;
        $balanceIn->ammount = $requestData['ammount'];
        // $balanceOut->description = boolval($requestData['detail']) ? $requestData['detail'] : null;
        $balanceIn->total = $userBalance->last()->total + $requestData['ammount']; //

        if (!$balanceIn->save()) return $this->internalError('Ocurrio un error');

        return $this->successResponse('Transferencia realizada con exito');
    }
}
