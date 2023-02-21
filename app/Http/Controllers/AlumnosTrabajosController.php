<?php

namespace App\Http\Controllers;

use App\Models\AlumnoTrabajo;
use App\Models\ProfesorTrabajo;
use App\Models\Trabajo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlumnosTrabajosController extends Controller {


    public function store(String $name, String $tt_id): bool {
        $alumno = new AlumnoTrabajo;
        $alumno->setAttribute('tt_id', $tt_id);
        $alumno->setAttribute('student_name', $name);
        return $alumno->save();
    }

    public function add(Request $request): JsonResponse {
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
            'alumnos' => 'required|array'
        ]);


        $trabajo_profesor = ProfesorTrabajo::where([
            ['tt_id', $request->get('tt_id')],
            ['profesor_id', $user->id]
        ])->get();

        if (count($trabajo_profesor) === 0) {
            return response()->json([
                'code' => 403,
                'message' => 'No puedes actualizar el trabajo terminal de otro profesor',
            ]);
        } else {
            $result = true;
            $nuevos_alumnos = $request->get('alumnos');
            foreach ($nuevos_alumnos as $alumno) {
                $result = $this->store($alumno, $request->get('tt_id'));
            }

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

    public function remove(Request $request): JsonResponse {
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
            'alumno_id' => 'required'
        ]);

        $trabajo_profesor = ProfesorTrabajo::where([
            ['tt_id', $request->get('tt_id')],
            ['profesor_id', $user->id]
        ])->get();

        $alumno = AlumnoTrabajo::where([
            ['id', $request->get('alumno_id')],
            ['tt_id', $request->get('tt_id')]
        ])->get();

        if (count($trabajo_profesor) === 0) {
            return response()->json([
                'code' => 403,
                'message' => 'No puedes actualizar el trabajo terminal de otro profesor',
            ]);
        } else {

            if (count($alumno) === 0) {
                return response()->json([
                    'code' => 404,
                    'message' => 'No se encontro al alumno',
                ]);
            }

            $result = $alumno[0]->delete();

            if ($result) {
                return response()->json([
                    'code' => 200,
                    'message' => 'Alumno eliminado correctamente',
                ]);
            } else {
                return response()->json([
                    'code' => 400,
                    'message' => 'Ocurrio un error, intentalo nuevamente',
                ]);
            }
        }
    }

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
            'alumno_id' => 'required',
            'alumno_name' => 'required'
        ]);

        $trabajo_profesor = ProfesorTrabajo::where([
            ['tt_id', $request->get('tt_id')],
            ['profesor_id', $user->id]
        ])->get();

        $alumno = AlumnoTrabajo::where([
            ['id', $request->get('alumno_id')],
            ['tt_id', $request->get('tt_id')]
        ])->get();

        if (count($trabajo_profesor) === 0) {
            return response()->json([
                'code' => 403,
                'message' => 'No puedes actualizar el trabajo terminal de otro profesor',
            ]);
        } else {

            if (count($alumno) === 0) {
                return response()->json([
                    'code' => 404,
                    'message' => 'No se encontro al alumno',
                ]);
            }

            $alumno[0]->setAttribute('student_name', $request->get('alumno_name'));
            $result = $alumno[0]->save();

            if ($result) {
                return response()->json([
                    'code' => 200,
                    'message' => 'Alumno actualizado correctamente',
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
