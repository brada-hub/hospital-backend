<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tratamientos', function (Blueprint $table) {
            $table->id();
            $table->string('tipo');
            $table->string('descripcion');
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin');
            $table->string('observaciones')->nullable();
       $table->unsignedTinyInteger('estado')->default(0);
            $table->foreignId('internacion_id')->constrained('internacions')->onDelete('cascade');
            $table->foreignId('user_id')->comment('ID del médico que prescribe')->constrained('users');
            $table->timestamps();
        });
    }
public function update(Request $request, $id)
    {
        $tratamiento = Tratamiento::findOrFail($id);

        $data = $request->validate([
            'tipo' => 'sometimes|required|string|max:100',
            'descripcion' => 'sometimes|required|string',
            'fecha_fin' => 'sometimes|required|date|after_or_equal:fecha_inicio',
            'observaciones' => 'nullable|string',
            'recetas' => 'present|array',
            'recetas.*.medicamento_id' => 'required|exists:medicamentos,id',
            'recetas.*.dosis' => 'required|string|max:100',
            'recetas.*.via_administracion' => 'required|string|max:100',
            'recetas.*.frecuencia_horas' => 'required|integer|min:1',
            'recetas.*.duracion_dias' => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($tratamiento, $data) {
                $tratamiento->update($data);
                $tratamiento->recetas()->delete();
                if (!empty($data['recetas'])) {
                    $tratamiento->recetas()->createMany($data['recetas']);
                }
            });

            Log::info('Tratamiento actualizado', ['id' => $tratamiento->id, 'user_id' => Auth::id()]);
            return response()->json(['message' => 'Tratamiento actualizado con éxito.'], 200);

        } catch (\Exception $e) {
            Log::error('Error al actualizar tratamiento:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error interno al actualizar.'], 500);
        }
    }

    /**
     * ✅ Tarea: Suspensión de tratamientos (sin cambiar la BD).
     * Finaliza un tratamiento actualizando su fecha_fin a ahora
     * y añadiendo el motivo a las observaciones.
     */
    public function suspender(Request $request, Tratamiento $tratamiento)
    {
        // La comparación ahora es con el número 0 (que definimos como 'activo')
        if ($tratamiento->estado !== 0) {
            return response()->json(['message' => 'Este tratamiento no está activo.'], 409);
        }

        $data = $request->validate(['motivo' => 'required|string|min:10']);
        $nuevasObservaciones = trim($tratamiento->observaciones . "\n\nSUSPENDIDO (" . now()->format('d/m/Y') . "): " . $data['motivo']);

        $tratamiento->update([
            'estado' => 1, // ✅ CAMBIO: Usamos el número 1 para 'suspendido'
            'observaciones' => $nuevasObservaciones
        ]);
        Log::warning('Tratamiento suspendido', ['id' => $id, 'motivo' => $data['motivo'], 'user_id' => Auth::id()]);
        return response()->json(['message' => 'Tratamiento suspendido con éxito.'], 200);
    }
    /**
     * Reverse the migrations.
     */

    public function down(): void
    {
        Schema::dropIfExists('tratamientos');
    }
};
