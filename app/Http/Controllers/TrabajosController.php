<?php

namespace App\Http\Controllers;

use App\Models\AlumnoTrabajo;
use App\Models\ProfesorTrabajo;
use App\Models\Trabajo;
use ErrorException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrabajosController extends Controller {

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse {
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

        return response()->json([
            'code' => 200,
            'message' => 'Listado de trabajos terminales',
            'data' => Trabajo::all()
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function get(Request $request, Trabajo $trabajo): JsonResponse {
        /*************************************************/
        // Validate user permissions and token
        $data = (new AuthController())->me();
        $user = $data->getData();
        try {
            if ($user->id == null) {
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
        $alumnos = AlumnoTrabajo::where([
            ['tt_id', $trabajo->id]
        ])->get();
        $trabajo->setAttribute('alumnos', $alumnos);

        return response()->json([
            'code' => 200,
            'message' => 'Listado de trabajos terminales',
            'data' => $trabajo
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse {
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
            'nombre' => 'required',
            'descripcion' => 'required',
            'tipo' => 'required|integer',
            'link' => 'required',
            'numero' => 'required',
            'alumnos' => 'required|array'
        ]);

        $trabajo_terminal = new Trabajo;
        $trabajo_terminal->setAttribute('name', $request->get('nombre'));
        $trabajo_terminal->setAttribute('description', $request->get('descripcion'));
        $trabajo_terminal->setAttribute('type', $request->get('tipo'));
        $trabajo_terminal->setAttribute('link', $request->get('link'));
        $trabajo_terminal->setAttribute('status', 0);
        $trabajo_terminal->setAttribute('tt_identificador', $request->get('numero'));
        $result = $trabajo_terminal->save();

        $alumnos = $request->get('alumnos');
        foreach ($alumnos as $alumno) {
            $insert = (new AlumnosTrabajosController())->store($alumno, $trabajo_terminal->id);
            if (!$insert) {
                return response()->json([
                    'code' => 400,
                    'message' => 'Ocurrio un error, intentalo nuevamente',
                ]);
            }
        }
        (new ProfesorTrabajosController())->store($user->id, $trabajo_terminal->id);

        if ($result) {
            return response()->json([
                'code' => 201,
                'message' => 'Trabajo terminal creado exitosamente',
            ], 201);
        } else {
            return response()->json([
                'code' => 400,
                'message' => 'Ocurrio un error, intentalo nuevamente',
            ]);
        }
    }

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
            'tt_id' => 'required'
        ]);

        $trabajo = Trabajo::where([
            ['id', $request->get('tt_id')]
        ])->get();

        $alumnos = DB::table('alumno_trabajos')
            ->where(
                'tt_id', $request->get('tt_id')
            )->get();

        $trabajo_profesor = ProfesorTrabajo::where([
            ['tt_id', $request->get('tt_id')],
            ['profesor_id', $user->id]
        ])->get();

        if (count($trabajo_profesor) === 0) {
            return response()->json([
                'code' => 403,
                'message' => 'No puedes eliminar el trabajo terminal de otro profesor',
            ]);
        } else {
            if (count($trabajo) === 0) {
                return response()->json([
                    'code' => 404,
                    'message' => 'No se encontro un trabajo terminal con este identificador',
                ]);
            } else {
                foreach ($alumnos as $alumno) {
                    DB::table('alumno_trabajos')
                        ->where(
                            'tt_id', $request->get('tt_id')
                        )->delete();
                }
                if (count($trabajo_profesor) > 0 ) {
                    $trabajo_profesor[0]->delete();
                }
                $result = $trabajo[0]->delete();


                if ($result) {
                    return response()->json([
                        'code' => 200,
                        'message' => 'Trabajo eliminado correctamente',
                    ]);
                } else {
                    return response()->json([
                        'code' => 400,
                        'message' => 'Ocurrio un error, intentalo nuevamente',
                    ]);
                }
            }
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse {
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
            'tt_id' => 'required',
            'nombre' => 'required',
            'descripcion' => 'required',
            'tipo' => 'required|integer',
            'link' => 'required'
        ]);

        $trabajo = Trabajo::where([
            ['id', $request->get('tt_id')]
        ])->get();

        $trabajo_profesor = ProfesorTrabajo::where([
            ['tt_id', $request->get('tt_id')],
            ['profesor_id', $user->id]
        ])->get();

        if (count($trabajo) === 0) {
            return response()->json([
                'code' => 404,
                'message' => 'No se encontro un trabajo terminal con este identificador',
            ]);
        } else {
            if (count($trabajo_profesor) === 0) {
                return response()->json([
                    'code' => 403,
                    'message' => 'No puedes actualizar el trabajo terminal de otro profesor',
                ]);
            } else {
                $trabajo[0]->setAttribute('name', $request->get('nombre'));
                $trabajo[0]->setAttribute('description', $request->get('descripcion'));
                $trabajo[0]->setAttribute('type', $request->get('tipo'));
                $trabajo[0]->setAttribute('link', $request->get('link'));

                if ($request->get('tipo') == 4) {
                    $trabajo[0]->setAttribute('status', 1);
                } else {
                    $trabajo[0]->setAttribute('status', 0);
                }

                $result = $trabajo[0]->save();

                if ($result) {
                    return response()->json([
                        'code' => 200,
                        'message' => 'Trabajo actualizado correctamente',
                    ]);
                } else {
                    return response()->json([
                        'code' => 400,
                        'message' => 'Ocurrio un error, intentalo nuevamente',
                    ]);
                }
            }
        }
    }
}
