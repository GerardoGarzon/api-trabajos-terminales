<?php

namespace App\Http\Controllers;

use App\Models\Profesor;
use App\Models\User;
use ErrorException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfesorsController extends Controller {

    /**
     * Crea un nuevo profesor utilizando el identificador del usuario registrado
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
            $profesor = new Profesor;
            $usuario = json_decode($registro[0]);
            $profesor->setAttribute('userId', $usuario->id);
            $profesor->save();
            return true;
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function addLinkProfesor(Request $request): JsonResponse {
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
            'tipo' => 'required',
            'data' => 'required'
        ]);

        $profesor_user = User::where([
            ['id', $user->id]
        ])->get();

        if ($request->get('tipo') == 0) { // PHONE
            $profesor_user[0]->setAttribute('phone', $request->get('data'));
            $profesor_user[0]->save();

            return response()->json([
                'code' => 200,
                'message' => 'Telefono actualizado correctamente'
            ]);
        } else {
            $profesor = Profesor::where([
                ['userId', $user->id]
            ])->get();

            if (count($profesor) === 0) {
                return response()->json([
                    'code' => 401,
                    'message' => 'Usuario no autorizado para cambiar este valor'
                ]);
            } else {
                $value = null;

                if ( $request->get('data') != "" ) {
                    $value = $request->get('data');
                }

                if ($request->get('tipo') == 1) { // GITHUB
                    $profesor[0]->setAttribute('githubURL', $value);
                    $profesor[0]->save();

                    return response()->json([
                        'code' => 200,
                        'message' => 'Github actualizado correctamente'
                    ]);
                } else if ($request->get('tipo') == 2) { // FILES
                    $profesor[0]->setAttribute('driveURL', $value);
                    $profesor[0]->save();

                    return response()->json([
                        'code' => 200,
                        'message' => 'Mis archivos actualizados correctamente'
                    ]);
                } else if ($request->get('tipo') == 3) { // UBICACION
                    $profesor[0]->setAttribute('location', $value);
                    $profesor[0]->save();

                    return response()->json([
                        'code' => 200,
                        'message' => 'Ubicación dentro de la escuela actualizada correctamente'
                    ]);
                } else {
                    return response()->json([
                        'code' => 400,
                        'message' => 'Ocurrio un error, intentalo nuevamente'
                    ]);
                }
            }
        }
    }
}
