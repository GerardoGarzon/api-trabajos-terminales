<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\User;
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
}
