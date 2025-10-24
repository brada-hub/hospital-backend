<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificacionController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->input('limit', 20);

        $notificaciones = Notificacion::where('user_id', Auth::id())
            ->with(['internacion.paciente', 'control'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json($notificaciones);
    }

    public function contarNoLeidas()
    {
        $count = Notificacion::where('user_id', Auth::id())
            ->noLeidas()
            ->count();

        return response()->json(['count' => $count]);
    }

    public function marcarComoLeida($id)
    {
        $notificacion = Notificacion::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $notificacion->marcarComoLeida();

        return response()->json(['message' => 'Notificación marcada como leída']);
    }

    public function marcarTodasComoLeidas()
    {
        Notificacion::where('user_id', Auth::id())
            ->noLeidas()
            ->update(['leida' => true]);

        return response()->json(['message' => 'Todas las notificaciones marcadas como leídas']);
    }

    public function eliminar($id)
    {
        $notificacion = Notificacion::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $notificacion->delete();

        return response()->json(['message' => 'Notificación eliminada']);
    }
}
