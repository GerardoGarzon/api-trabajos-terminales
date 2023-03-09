<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Preregistro;
use App\Models\User;
use ErrorException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlumnosController extends Controller {

    /**
     * Crea un nuevo alumno utilizando el identificador del usuario registrado
     *
     * @param Request $request
     * @return bool
     */
    public function store(Request $request): bool {
        $registro = User::where([
            ['email', $request->get('email')]
        ])->get();

        if (count($registro) === 0) {
            return false;
        } else {
            $alumno = new Alumno;
            $usuario = json_decode($registro[0]);
            $alumno->setAttribute('userId', $usuario->id);
            $alumno->setAttribute('boleta', $request->get('boleta'));
            $alumno->setAttribute('is_active', false);
            $alumno->save();
            return true;
        }
    }

    /**
     * Activa la cuenta del alumno para que pueda iniciar sesión
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function activate(Request $request): JsonResponse {
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

        $request->validate([
            'email' => 'required',
            'alumno_email' => 'required'
        ]);

        $alumno =  User::where([
            ['email', $request->get('alumno_email')]
        ])->get();

        if ( count($alumno) === 0 ) {
            return response()->json([
                'code' => 400,
                'message' => 'Alumno no encontrado'
            ]);
        } else {
            $usuario = json_decode($alumno[0]);
            $alumno_info = Alumno::where([
                ['userId', $usuario->id]
            ])->get();

            if (count($alumno_info) === 0) {
                return response()->json([
                    'code' => 400,
                    'message' => 'Alumno no encontrado'
                ]);
            } else {
                $alumno_info[0]->setAttribute('is_active', true);
                $alumno_info[0]->save();

                $preregistro = Preregistro::where([
                    ['email', $request->get('alumno_email')]
                ])->get();

                if (count($preregistro) > 0) {
                    $preregistro[0]->delete();
                }

                return  response()->json([
                    'code' => 200,
                    'message' => 'Alumno activado exitosamente'
                ]);
            }
        }
    }

    /**
     * Elimina a un alumno, unicamente puede ser ejecutada por profesores
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse {
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

        $request->validate([
            'alumno_email' => 'required'
        ]);

        $preregistro = Preregistro::where([
            ['email', $request->get('alumno_email')]
        ])->get();

        if (count($preregistro) > 0) {
            $preregistro[0]->delete();
        }

        $usuario = User::where([
            ['email', $request->get('alumno_email')]
        ])->get();

        if (count($usuario) > 0) {
            $alumno_id = json_decode($usuario[0]);
            $alumno = Alumno::where([
                ['userId', $alumno_id->id]
            ])->get();
            $alumno[0]->delete();
            $usuario[0]->delete();
            return response()->json([
                'code' => 200,
                'message' => 'Alumno eliminado exitosamente'
            ]);
        } else {
            return response()->json([
                'code' => 400,
                'message' => 'Alumno no encontrado'
            ]);
        }
    }

    /**
     * Elimina a un preregistro, unicamente puede ser ejecutada por profesores
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deletePreregister(Request $request): JsonResponse {
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

        $request->validate([
            'alumno_email' => 'required'
        ]);

        $preregistro = Preregistro::where([
            ['email', $request->get('alumno_email')]
        ])->get();

        if (count($preregistro) > 0) {
            $preregistro[0]->delete();

            $usuario = User::where([
                ['email', $request->get('alumno_email')]
            ])->get();

            if (count($usuario) > 0) {
                $alumno_id = json_decode($usuario[0]);
                $alumno = Alumno::where([
                    ['userId', $alumno_id->id]
                ])->get();
                $alumno[0]->delete();
                $usuario[0]->delete();
                return response()->json([
                    'code' => 200,
                    'message' => 'Alumno eliminado exitosamente'
                ]);
            } else {
                return response()->json([
                    'code' => 400,
                    'message' => 'Alumno no encontrado'
                ]);
            }
        } else {
            return response()->json([
                'code' => 400,
                'message' => 'Alumno no encontrado'
            ]);
        }
    }
}
