<?php

namespace App\Http\Controllers;

use App\Models\AddressBook;
use App\Models\User;
use Illuminate\Http\Request;

use function PHPUnit\Framework\isNull;

class AddressBookController extends Controller
{
    private $contactName = '';

    public function getContacts(Request $request) {
        $name = $request->query('name', null);
        $user = $request->user();
        $contacts = $user->adressBook();

        if ($contacts->isEmpty()) return $this->successGet($contacts);

        $contactList = array();
        foreach ($contacts as $contact) {
            $contactData = User::where('cvu',$contact->cvu)->first();
            $contactInformation = array(
                "id" => $contactData->id,
                "name" => $contactData->name . ' ' . $contactData->lastName,
                "cvu" => $contactData->cvu,
                "alias" => $contactData->alias,
            );
            array_push($contactList,$contactInformation);
        }
        if (!is_null($name)) {
            $this->contactName = $name;
            $contactList = array_filter($contactList, function ($contact){
                return str_contains($contact['name'],$this->contactName);
            });
        }
        return $this->successGet($contactList);
    }

    public function addContact($user = null, $cvu = null){
        if(is_null($cvu)) return false;
        if(is_null($user)) return false;

        $recordExist = AddressBook::where('user_id',$user->id)->where('cvu',$cvu)->first();
        if(!is_null($recordExist)) return true;

        $newRecord = new AddressBook();
        $newRecord->user_id = $user->id;
        $newRecord->cvu = $cvu;

        if($newRecord->save()) return true;

        return false;
    }

    public function addContactToUser(Request $request) {
        $user = $request->user();
        $requestData = $request->only('cvu','alias');
        if(empty($requestData)) return $this->badRequest('Se debe enviar almenos un cvu o alias');
        if(!array_key_exists('alias',$requestData)){
            if (!array_key_exists('cvu', $requestData)) {
                return $this->badRequest('Se debe indicar un cvu o alias');
            }
            if(is_null($requestData['cvu'])) return $this->badRequest('Se debe indicar un cvu o alias');
            $cvu = $requestData['cvu'];
        }else{
            $contact = User::where('alias',$requestData['alias'])->first();
            if(is_null($contact)) return $this->badRequest('No se encontro al usuario');
            $cvu = $contact->cvu;
        };

        if($this->addContact($user,$cvu)) return $this->successResponse('Contacto añadido conexito');
        return $this->internalError('Ocurrio un error');
    }
}
