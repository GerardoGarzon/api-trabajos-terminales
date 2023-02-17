<?php

namespace App\Http\Controllers;

use App\Models\Profesor;
use App\Models\User;
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
}
