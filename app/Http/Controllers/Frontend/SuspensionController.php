<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Suspensione;

class SuspensionController extends Controller
{
    function consulta_suspension(){
        
        return view('frontend.suspension.all');

    }
	
	public function actualizarSuspensionAgremiado(Request $request)
    {
        $secureToken = config('values.cron_security_token');

        if ($request->query('token') !== $secureToken) {
            return response()->json(['error' => 'No autorizado.'], 401);
        }

		$suspensione_model = new Suspensione;
		$suspensione_model->actualizarSuspensionAgremiado();

        return response()->json(['success' => 'Estados actualizados correctamente.']);
    }
	
}
