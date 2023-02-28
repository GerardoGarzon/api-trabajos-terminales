<?php

namespace App\Http\Controllers;

use App\Models\Preregistro;
use App\Models\User;
use App\Utils\AppUtils;
use ErrorException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class PreregistroController extends Controller {

    /**
     * Muestra la lista de preregistros
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse {
        /*************************************************/
        // Validate user permissions and token
        $data = (new AuthController())->me();
        $user = $data->getData();
        try {
            if ($user->type == 0) {
                return response()->json([
                    'code' => 401,
                    'message' => 'Usuario no autorizado'
                ], 401);
            }
        } catch (ErrorException $ex) {
            return response()->json([
                'code' => 401,
                'message' => 'Usuario no autorizado'
            ], 401);
        }
        /*************************************************/

        $preregistros = Preregistro::all();
        return response()->json([
            'code' => 200,
            'message' => 'Operacion realizada con exito',
            'data' => $preregistros
        ]);
    }

    /**
     * Crea un nuevo preregistro utilizando el email y la contraseña, si ya existe un preregistro
     * se actualiza el OTP
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
            'isAlumno' => 'required'
        ]);

        if (!preg_match('([a-zA-Z0-9]+(@alumno.ipn.mx))', $request->get('email'))) {
            return response()->json([
                'code' => 400,
                'message' => 'Correo institucional invalido'
            ]);
        }

        if ( $request->get('isAlumno') ){
            $request->validate([
                'idProfesor' => 'required'
            ]);
        }

        $registro = User::where([
            ['email', $request->get('email')]
        ])->get();

        if (count($registro) > 0) {
            return response()->json([
                'code' => 400,
                'message' => 'Ya existe un usuario registrado con el email'
            ]);
        }

        try {
            $preregistro = new Preregistro;
            $preregistro->setAttribute('email', $request->get('email'));
            $preregistro->setAttribute('password', bcrypt($request->get('password')));
            $preregistro->setAttribute('otp', AppUtils::generateOTP(5));
            $preregistro->setAttribute('is_student', $request->get('isAlumno'));
            if ( $request->get('isAlumno') ){
                $preregistro->setAttribute('auth_profesor', $request->get('idProfesor'));
            }
            $result = $preregistro->save();
        } catch (QueryException $e){
            return $this->update($request);
        }

        if ($result) {
            return response()->json([
                'code' => 201,
                'message' => 'Registro exitoso, se envio un codigo al correo indicado'
            ], 201);
        } else {
            return response()->json([
                'code' => 400,
                'message' => 'Ocurrio un error en tu registro'
            ], 200);
        }
    }

    /**
     * Valida y crea un nuevo registro de usuario, si el tipo de usuario en el preregistro es un alumno
     * se genera un registro en la tabla de alumnos, en caso contrario se crea un registro para profesor
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse {
        $request->validate([
            'token' => 'required',
            'email' => 'required',
            'nombre' => 'required',
            'telefono' => 'required'
        ]);

        $preregistro = Preregistro::where([
            ['email', $request->get('email')]
        ])->get();

        if (count($preregistro) === 0) {
            return response()->json([
                'code' => 400,
                'message' => 'No existe un preregistro con este email'
            ]);
        }

        if ( $preregistro[0]->is_student ) {
            $request->validate([
                'boleta' => 'required'
            ]);
        }

        $registro = User::where([
            ['email', $request->get('email')]
        ])->get();

        if (count($registro) > 0) {
            return response()->json([
                'code' => 400,
                'message' => 'Ya existe un usuario registrado con el email'
            ]);
        }

        if ($preregistro[0]->token != $request->get('token')) {
            return response()->json([
                'code' => 400,
                'message' => 'Ocurrio un error durante tu registro, intenta nuevamente'
            ]);
        } else {
            $registro = new User;
            $registro->setAttribute('name', $request->get('nombre'));
            $registro->setAttribute('email', $request->get('email'));
            $registro->setAttribute('password', $preregistro[0]->password);
            $registro->setAttribute('phone', $request->get('telefono'));
            if ( $preregistro[0]->is_student ) {
                $registro->setAttribute('type', 0);
            } else {
                $registro->setAttribute('type', 1);
                $preregistro[0]->delete();
            }
            $registro->save();

            if ( $preregistro[0]->is_student ) {
                $result = (new AlumnosController)->store($request);
            } else {
                $result = (new ProfesorsController)->store($request);
            }

            if ($result) {
                return response()->json([
                    'code' => 201,
                    'message' => 'Registro de usuario exitoso'
                ]);
            } else {
                return response()->json([
                    'code' => 400,
                    'message' => 'Ocurrio un error durante tu registro, intenta nuevamente'
                ]);
            }
        }
    }

    /**
     * Actualiza el codigo OTP y reenvia el correo
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse {
        $request->validate([
            'email' => 'required'
        ]);

        $preregistro = Preregistro::where('email', $request->get('email'))->get();

        if (count($preregistro) === 0) {
            return response()->json([
                'code' => 404,
                'message' => 'No se encontro un registro con ese correo'
            ]);
        } else {
            $preregistro[0]->setAttribute('otp', AppUtils::generateOTP(5));
            $preregistro[0]->save();

            return response()->json([
                'code' => 200,
                'message' => 'Se reenvio exitosamente tu codigo'
            ]);
        }
    }

    /**
     * Verifica si el codigo OTP es valido, de ser asi genera un token con el que se puede
     * completar el registro
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function verify(Request $request): JsonResponse {
        $request->validate([
            'email' => 'required',
            'otp' => 'required'
        ]);

        $preregistro = Preregistro::where([
            ['email', $request->get('email')],
            ['otp', $request->get('otp')]
        ])->get();

        if ( count($preregistro) === 0 ) {
            return response()->json([
                'code' => 404,
                'message' => 'Codigo incorrecto'
            ]);
        } else {
            $token = md5(uniqid(rand(), true));
            $preregistro[0]->setAttribute('token', $token);
            $preregistro[0]->save();
            return response()->json([
                'code' => 200,
                'message' => 'Codigo OTP valido',
                'token' => $token
            ]);
        }
    }
}
