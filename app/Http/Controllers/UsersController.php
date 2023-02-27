<?php

namespace App\Http\Controllers;

use App\Models\Preregistro;
use App\Models\User;
use ErrorException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\UserNotDefinedException;

class UsersController extends Controller {

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

        $preregistros = Preregistro::where([
            ['auth_profesor', $user->id]
        ])->get();

        return response()->json([
            'code' => 200,
            'message' => 'Usuarios registrados',
            'data' => $preregistros
        ]);
    }

    public function getProfesors(Request $request) {
        $usuarios = User::where([
            ['type', 1]
        ])->get();

        return response()->json([
            'code' => 200,
            'message' => 'Lista de profesores',
            'data' => $usuarios
        ]);
    }
}
