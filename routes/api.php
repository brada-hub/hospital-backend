<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    RolController,
    UserController,
    PacienteController,
    InternacionController,
    MedicamentoController,
    SignoController,
    AlimentacionController,
    HospitalController,
    TratamientoController,
    ControlController,
    ValorController,
    ConsumeController,
    CuidadoController,
    EspecialidadController,
    SalaController,
    CamaController,
    OcupacionController,
    RecetaController,
    AdministraController,
    CuidadoAplicadoController
};

// Rutas públicas (ping, login, registro)
Route::get('/ping', fn () => response()->json(['pong' => true]));
Route::post('/login', [UserController::class, 'login']);
Route::post('/register', [UserController::class, 'store']);

// Rutas protegidas con Sanctum
Route::middleware('auth:sanctum')->group(function () {
    // Roles y usuarios
    Route::apiResource('roles', RolController::class);
    Route::apiResource('usuarios', UserController::class);

    // Recursos hospitalarios
    Route::apiResource('pacientes', PacienteController::class);
    Route::apiResource('internaciones', InternacionController::class);
    Route::apiResource('medicamentos', MedicamentoController::class);
    Route::apiResource('signos', SignoController::class);
    Route::apiResource('alimentaciones', AlimentacionController::class);
    Route::apiResource('hospitals', HospitalController::class);
    Route::apiResource('tratamientos', TratamientoController::class);
    Route::apiResource('controls', ControlController::class);
    Route::apiResource('valores', ValorController::class);
    Route::apiResource('consumes', ConsumeController::class);
    Route::apiResource('cuidados', CuidadoController::class);
    Route::apiResource('especialidades', EspecialidadController::class);
    Route::apiResource('salas', SalaController::class);
    Route::apiResource('camas', CamaController::class);
    Route::apiResource('ocupaciones', OcupacionController::class);
    Route::apiResource('recetas', RecetaController::class);
    Route::apiResource('administraciones', AdministraController::class);
    Route::apiResource('cuidados-aplicados', CuidadoAplicadoController::class);

    // Logout
    Route::post('/logout', [UserController::class, 'logout']);
});
