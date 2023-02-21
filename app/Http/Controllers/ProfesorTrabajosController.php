<?php

namespace App\Http\Controllers;

use App\Models\ProfesorTrabajo;
use Illuminate\Http\Request;

class ProfesorTrabajosController extends Controller {

    public function store(String $profesor, String $trabajo) {
        $profesor_trabajo = new ProfesorTrabajo;
        $profesor_trabajo->setAttribute('profesor_id', $profesor);
        $profesor_trabajo->setAttribute('tt_id', $trabajo);
        return $profesor_trabajo->save();
    }

}
