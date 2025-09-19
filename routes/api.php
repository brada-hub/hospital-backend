<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    RolController,
    UserController,
    PermissionController,
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
    CuidadoAplicadoController,
    DashboardController,
    AdmisionController,
    MedicamentoCategoriaController
};


// Rutas públicas
Route::get('/ping', fn() => response()->json(['pong' => true]));
Route::post('/login', [UserController::class, 'login']);
Route::post('/register', [UserController::class, 'store']);
Route::get('/hospital-details/{id}', [HospitalController::class, 'getHospitalDetails']);

// Rutas protegidas con Sanctum
Route::middleware('auth:sanctum')->group(function () {

    // --- RUTAS PERSONALIZADAS Y ESPECÍFICAS (VAN PRIMERO) ---

    // Rutas para el Módulo de Admisión
    Route::get('/camas-disponibles', [CamaController::class, 'getDisponibles']);
    Route::get('/pacientes/buscar', [PacienteController::class, 'buscar']);
    Route::post('/admisiones', [AdmisionController::class, 'store']);

    // Rutas del Dashboard
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/kpis', [DashboardController::class, 'getKpis']);
        Route::get('/ocupacion-especialidad', [DashboardController::class, 'getOcupacionPorEspecialidad']);
        Route::get('/estado-camas', [DashboardController::class, 'getEstadoCamas']);
        Route::get('/ultimos-ingresos', [DashboardController::class, 'getUltimosIngresos']);
    });

    // Rutas de usuario y roles
    Route::put('/user/profile', [UserController::class, 'updateProfile']);
    Route::put('/user/password', [UserController::class, 'updatePassword']);
    Route::patch('/users/{user}/estado', [UserController::class, 'toggleEstado']);
    Route::get('/me', [UserController::class, 'me']);
    Route::post('/logout', [UserController::class, 'logout']);
    Route::put('/rols/{rol}/permissions', [RolController::class, 'syncPermissions']);
    Route::put('/users/{user}/permissions', [UserController::class, 'syncPermissions']);


    // --- RUTAS DE RECURSOS (apiResource) (VAN DESPUÉS) ---

    Route::apiResource('rols', RolController::class); // O 'rols', decide cuál usar
    Route::apiResource('users', UserController::class); // O 'users'
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
    Route::apiResource('permissions', PermissionController::class);
    Route::apiResource('medicamento-categorias', MedicamentoCategoriaController::class);

    // NOTA: He eliminado los apiResource duplicados para 'rols' y 'users'
    // que tenías al final para evitar confusiones.
});
