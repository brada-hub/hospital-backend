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
    AdmisionController
};


// Rutas públicas (ping, login, registro)
Route::get('/ping', fn () => response()->json(['pong' => true]));
Route::post('/login', [UserController::class, 'login']);
Route::post('/register', [UserController::class, 'store']);
// En web.php o api.php
Route::get('/hospital-details/{id}', [HospitalController::class, 'getHospitalDetails']);

// Rutas protegidas con Sanctum
Route::middleware('auth:sanctum')->group(function () {
    // Roles y usuarios
    Route::apiResource('roles', RolController::class);
    Route::apiResource('usuarios', UserController::class);
    Route::patch('/users/{user}/estado', [UserController::class, 'toggleEstado']);
    Route::get('/me', [UserController::class, 'me']);

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
       // ✅ AÑADIR ESTAS 3 RUTAS PARA EL MÓDULO DE ADMISIÓN
    Route::get('/camas-disponibles', [CamaController::class, 'getDisponibles']);
    Route::get('/pacientes/buscar', [PacienteController::class, 'buscar']);
    Route::post('/admisiones', [AdmisionController::class, 'store']);
    // Logout
    Route::post('/logout', [UserController::class, 'logout']);

     // Nuevas rutas de Permisos
    Route::apiResource('permissions', PermissionController::class);

    // Rutas actualizadas para Roles
    Route::apiResource('rols', RolController::class);
    Route::put('/rols/{rol}/permissions', [RolController::class, 'syncPermissions']);

    // Rutas actualizadas para Usuarios
    Route::apiResource('users', UserController::class);
    Route::put('/users/{user}/permissions', [UserController::class, 'syncPermissions']);
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/kpis', [DashboardController::class, 'getKpis'])->name('kpis');
        Route::get('/ocupacion-especialidad', [DashboardController::class, 'getOcupacionPorEspecialidad'])->name('ocupacion-especialidad');
        Route::get('/estado-camas', [DashboardController::class, 'getEstadoCamas'])->name('estado-camas');
        Route::get('/ultimos-ingresos', [DashboardController::class, 'getUltimosIngresos'])->name('ultimos-ingresos');
    });
});

// routes/api.php

