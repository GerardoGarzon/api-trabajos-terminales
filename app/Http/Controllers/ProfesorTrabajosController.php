<?php

namespace App\Http\Controllers;

use App\Models\Profesor;
use App\Models\ProfesorTrabajo;
use App\Models\Trabajo;
use App\Models\User;
use ErrorException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfesorTrabajosController extends Controller {

    /**
     * @param String $profesor
     * @param String $trabajo
     * @return bool
     */
    public function store(String $profesor, String $trabajo) {
        $profesor_trabajo = new ProfesorTrabajo;
        $profesor_trabajo->setAttribute('profesor_id', $profesor);
        $profesor_trabajo->setAttribute('tt_id', $trabajo);
        return $profesor_trabajo->save();
    }

    /**
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function getProfesorTrabajos(Request $request, User $user): JsonResponse {
        /*************************************************/
        // Validate user permissions and token
        $data = (new AuthController())->me();
        $user_me = $data->getData();

        try {
            if ($user_me->id == null) {
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

        $profesor_trabajos = ProfesorTrabajo::where([
            ['profesor_id', $user->id]
        ])->get();

        $trabajos = [];

        foreach ($profesor_trabajos as $relacion) {
            $trabajo = Trabajo::where([
                ['id', $relacion->tt_id]
            ])->get();
            array_push($trabajos, [
                'id' => $trabajo[0]->id,
                'nombre' => $trabajo[0]->name,
                'type' => $trabajo[0]->type,
                'status' => $trabajo[0]->status,
                'identificado' => $trabajo[0]->tt_identificador
            ]);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Trabajos terminales por profesor',
            'data' => $trabajos
        ]);
    }

}
