<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use App\Models\User;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    protected $alfabet = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','ñ','o','p','q','r','s','t','u','v','w','x','y','z');

    // private function generateAlias();

    public function register(Request $request){
        $request_data = $request->all();

        if(!empty($request_data)){

            $validacion = Validator::make($request_data,[
                'name'        =>  'required|regex:/^[\pL\s\-]+$/u',
                'lastName'      =>  'required|regex:/^[\pL\s\-]+$/u',
                'password'      =>  'required',
                'passwordCheck'      =>  'required',
                'email'         =>  'required|unique:users',
                'birthdate'    => 'required',
            ]);

            if($validacion->fails()){
                return $this->badRequest('Revise los datos enviados',$validacion->errors());
            }else{
                $password_cifrada = hash('sha256',$request_data['password']);

                $final_alias_a = rand(intval(0),intval(26));
                $final_alias_b = rand(intval(0),intval(26));
                $alias = $request_data['name'] . '.' . $request_data['lastName'] . '.' . $this->alfabet[$final_alias_a] . $this->alfabet[$final_alias_b];

                $user =new User();

                $user->name       = $request_data['name'];
                $user->lastName     = $request_data['lastName'];
                $user->password     = $password_cifrada;
                $user->email        = $request_data['email'];
                $user->birthdate   = $request_data['birthdate'];
                $user->economicProfile   = 1;
                $user->cvu   = rand(intval(1000000000000000000000), intval(9999999999999999999999));
                $user->alias   = $alias;

                try{
                    if ($user->save()) {
                        $newUser = User::where('email',$user->email)->first();
                        $token = $newUser->createToken('auth-token')->plainTextToken;
                        $response = array(
                            "user" => $user,
                            "token" => $token,
                        );

                        return $this->successCreation('Usuario registrado con exito', $response);
                    } else {
                        return $this->internalError('Ocurrio un error');
                    }
                }catch(Error $error){
                    return $this->internalError('Ocurrio un error');
                }
            }

        }else{
            return $this->badRequest('No se enviaron datos');
        }
    }

    public function login(Request $request){
        $request_data = $request->all();

        if(!empty($request_data)){
            $validacion = Validator::make($request_data,[
                'email' => 'required',
                'password' => 'required'
            ]);

            if($validacion->fails()){
                return $this->badRequest('Revise los datos enviados',$validacion->errors());
            }else{
                $password_cifrada= hash('sha256',$request_data['password']);

                if(!is_null(User::where(['email' => $request_data['email'], 'password' => $password_cifrada])->first())){

                    $user = User::where('email',$request_data['email'])->first();
                    $token = $user->createToken('auth-token')->plainTextToken;
                    return $this->apiLoginSuccess($user,$token);
                }else{
                    return $this->badRequest('Usuario o contraseña incorrectos');
                }
            }
        }else{
            return $this->error('No se enviaron datos');
        }
    }

    public function update(Request $request){

        $request_data = $request->only('name','lastName','birthdate','alias');

        $user = $request->user();

        $validacion = Validator::make($request_data,[
            'name'        =>'required',
            'lastName'      =>'required',
            'birthdate'    =>'required',
            'alias'      =>'required',
        ]);

        if($validacion->fails()){
            return $this->badRequest('Revise los datos ingresados',$validacion->errors());
        }else{
            unset($request_data['email']);
            unset($request_data['password']);
            unset($request_data['created_at']);
            unset($request_data['cvu']);


            $checkRepeatedAlias = User::where('alias',$request_data['alias'])->where('id','<>',$user->id)->get();
            if($checkRepeatedAlias->isNotEmpty()) return $this->badRequest('El alias indicado no puede ser utilizado');

            $updated_user = User::where('id',$user->id)->update($request_data);

            if($updated_user == 1){
                $updated_user = User::find($user->id);
                return $this->successResponse('Datos actualizados',$updated_user);
            }else{
                return $this->internalError('Ocurrio un error');
            }

        }
    }

    public function updateImage(Request $request){
        $user = $request->user();
        $imagen = $_FILES['fotoPerf'];
        if(!is_null($imagen)){
            if(!is_null($user->foto)){
                $urlPick = $user->foto;
                Storage::delete([$urlPick]);
            }
            move_uploaded_file($imagen['tmp_name'],storage_path().'/app/ProfileImages/'.$imagen['name']);
            $ruta = '/ProfileImages/'.$_FILES['fotoPerf']['name'];
            $user_updated = User::where('id',$user->id)->update(['foto'=>$ruta]);
            if($user_updated){
                $response = $this->success('Respuesta',$ruta,'foto');
            }else{
                $response = $this->error('Ocurrio un error');
            }
        }else{
            $response = $this->error('Debe ser enviado un archivo de imagen');
        }
        return response()->json($response,$response['code']);
    }

    public function logout(Request $request) {

    }

    public function askPasswordRecovery(Request $request){
        $jwt = New JwtAuth();

        $request_data = $request->all();
        $email = $request_data['email'];

        $passSend = $jwt->sendPasswordResetLink($email);

        return $this->successResponse('Email enviado');
    }

    public function resetPassword(Request $request){
        $jwt = New JwtAuth();
        $token = $request->header('Authorization');
        $user = $jwt->checkToken($token,true);

        $request_data = $request->all();

        $validacion = Validator::make($request_data,[
            'pwd' => 'required'
        ]);

        if($validacion->fails()){
            return $this->badRequest('Debe indicar una contraseña');
        }else{
            $password = $request_data['pwd'];
            $password_cifrada = hash('sha256',$password);
            $password_update = User::where('email',$user->email)->update(['password'=>$password_cifrada]);
            return $this->successResponse('Contraseña actualizada');
        }
    }
}
