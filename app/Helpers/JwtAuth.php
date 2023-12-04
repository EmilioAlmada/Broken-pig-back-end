<?php

namespace App\Helpers;

use App\Mail\PasswordMail;
use App\Mail\AcountConfirmationMail;
use App\Mail\ChangeMailNotificationMail;
use App\Models\IvitedUser;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Mail;

class JwtAuth{

    public $key;

    public function __construct(){
        $this->key = 'Pigy-65464';
    }

    public function sendPasswordResetLink($email){
        $user = User::where('email', $email)->first();

        if($user){
            $datos_token = array(
                'id' => $user->id,
                'email' => $user->email,
                'iat' => time(),
                'exp' => time() + (10 * 60)
            );

            $token = JWT::encode($datos_token,$this->key,'HS256');
            $datos = array(
                'token' => $token
            );
            Mail::to($user->email)->send(new PasswordMail($datos));

            return true;

        }else{
            return false;
        }
    }

    public function checkToken($token,$getIdentity=false){

        $auth = false;
        try{
            $jwt_token = str_replace('"','',$token);

            // $decoded_data = JWT::decode($jwt_token,$this->key,'HS256');
            $decoded_data = JWT::decode($jwt_token,New Key($this->key,'HS256'));
        } catch (\UnexpectedValueException $exception){
            $auth = false;
        }catch(\DomainException $exception){
            $auth = false;
        }
        if(!empty($decoded_data) && is_object($decoded_data) && isset($decoded_data->id)){
            $auth = true;
        }else{
            $auth = false;
        }

        if($getIdentity && $auth){
            return $decoded_data;
        }

        return $auth;
    }
}
