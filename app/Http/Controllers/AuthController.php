<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller {

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['login', 'me', 'inicio']]);
    }

    /**
     * Obtiene el token JWT utilizando las credenciales de acceso, si el usuario es alumno
     * y aun no esta activa su cuenta no puede obtener el token JWT
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login() {
        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json([
                'code' => 401,
                'message' => 'Credenciales invalidas'
            ]);
        }

        $user_data = User::where([
            ['email', $credentials['email']]
        ])->get();

        if ($user_data[0]->type == 0) {
            $usuario = json_decode($user_data[0]);
            $alumno = Alumno::where([
                ['userId', $usuario->id]
            ])->get();
            if ($alumno[0]->is_active == 0) {
                return response()->json([
                    'code' => 400,
                    'message' => 'Solicita la activación de tu cuenta a tu profesor'
                ]);
            } else {
                return $this->respondWithToken($token, true);
            }
        }

        return $this->respondWithToken($token, false);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me() {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout() {
        auth()->logout();

        return response()->json(['message' => 'Sesion terminada']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh() {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken(string $token, bool $isAlumno) {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() *0,
            'isAlumno' => $isAlumno
        ]);
    }

    /**
     * Get deploy day
     */
    public function inicio() {
        return response()->json([
            'inicio' => 'OK',
            'deploy' => '09/06/2023'
        ]);
    }
}
