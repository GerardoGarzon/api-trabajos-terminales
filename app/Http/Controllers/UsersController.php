<?php

namespace App\Http\Controllers;

use App\Models\Preregistro;
use App\Models\Profesor;
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
            ['auth_profesor', $user->id],
            ['isCompleted', true]
        ])->get();

        return response()->json([
            'code' => 200,
            'message' => 'Usuarios registrados',
            'data' => $preregistros
        ]);
    }

    public function getProfesors(Request $request) {
        if ($request->has('page')) {
            $profesors_array = array();
            $pagination_data = User::where([
                ['type', 1]
            ])->paginate(9);
            $pages = $pagination_data->lastPage();
            $actualPage = $pagination_data->currentPage();

            $usuarios = $pagination_data->items();
        } else {
            $profesors_array = array();
            $usuarios = User::where([
                ['type', 1]
            ])->get();
            $pages = 0;
            $actualPage = 0;
        }

        foreach ($usuarios as $usuario) {
            $profesor = Profesor::where([
                ['userId', $usuario->id]
            ])->get();

            array_push($profesors_array, [
                'id' => $usuario->id,
                'name' => $usuario->name,
                'email' => $usuario->email,
                'phone' => $usuario->phone,
                'type' => $usuario->type,
                'githubURL' => $profesor[0]->githubURL,
                'driveURL' => $profesor[0]->driveURL,
                'location' => $profesor[0]->location,
            ]);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Lista de profesores',
            'data' => $profesors_array,
            'numberPages' => $pages,
            'activePage' => $actualPage
        ]);
    }

    /**
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function getProfesorDetail(Request $request, User $user): JsonResponse {
        $profesor = Profesor::where([
            ['userId', $user->id]
        ])->get();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'type' => $user->type,
            'githubURL' => $profesor[0]->githubURL,
            'driveURL' => $profesor[0]->driveURL,
            'location' => $profesor[0]->location,
        ]);
    }

}
