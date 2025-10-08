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
    HospitalController,
    TratamientoController,
    ControlController,
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
    MedicamentoCategoriaController,
    SeguimientoController,

    // --- AÑADIR ESTOS TRES NUEVOS CONTROLADORES ---
    TipoDietaController,
    AlimentacionController,
    ConsumeController
};

// Rutas públicas
Route::get('/ping', fn() => response()->json(['pong' => true]));
Route::post('/login', [UserController::class, 'login']);
Route::post('/register', [UserController::class, 'store']);
Route::get('/hospital-details/{id}', [HospitalController::class, 'getHospitalDetails']);

// Rutas protegidas con Sanctum
Route::middleware('auth:sanctum')->group(function () {

    // --- RUTAS PERSONALIZADAS Y ESPECÍFICAS ---
    Route::get('/me', [UserController::class, 'me']);
    Route::post('/logout', [UserController::class, 'logout']);

    // Admisión
    Route::get('/camas-disponibles', [CamaController::class, 'getDisponibles']);
    Route::get('/pacientes/buscar', [PacienteController::class, 'buscar']);
    Route::get('/medicos-activos', [UserController::class, 'getMedicosActivos']);
    Route::post('/admisiones', [AdmisionController::class, 'store']);
    Route::get('/internaciones/{internacion}/vista-completa', [InternacionController::class, 'getVistaCompleta']);
    Route::post('/internaciones/{internacion}/dar-de-alta', [InternacionController::class, 'darDeAlta']);

    // Dashboard Médico
    Route::get('/mis-pacientes', [InternacionController::class, 'getMisPacientes']);
    Route::get('/internaciones/{id}/dashboard', [InternacionController::class, 'getDashboardData']);
    Route::post('/tratamientos/{tratamiento}/suspender', [TratamientoController::class, 'suspender']);
    Route::post('/tratamientos/{tratamiento}', [TratamientoController::class, 'update']);

    // Estación de Enfermería
    Route::get('/estacion-enfermeria/pacientes', [InternacionController::class, 'getPacientesParaEnfermeria']);
    Route::post('/cuidados-directo', [CuidadoController::class, 'storeAplicadoDirecto']);

    // Seguimiento
    Route::get('/seguimiento/tratamiento/{id}', [SeguimientoController::class, 'getEstadoTratamiento']);
    Route::post('/alimentaciones/{alimentacion}/suspender', [AlimentacionController::class, 'suspender']);
    // Generar cronograma manual
    Route::post('/cronograma/generar/{receta}', [AdministraController::class, 'generarCronograma']);
    // Dashboard Principal
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/kpis', [DashboardController::class, 'getKpis']);
        Route::get('/ocupacion-especialidad', [DashboardController::class, 'getOcupacionPorEspecialidad']);
        Route::get('/estado-camas', [DashboardController::class, 'getEstadoCamas']);
        Route::get('/ultimos-ingresos', [DashboardController::class, 'getUltimosIngresos']);
    });

    // Administración de Roles y Permisos
    Route::put('/rols/{rol}/permissions', [RolController::class, 'syncPermissions']);
    Route::put('/users/{user}/permissions', [UserController::class, 'syncPermissions']);
    Route::patch('/users/{user}/estado', [UserController::class, 'toggleEstado']);


    // --- RUTAS DE RECURSOS (apiResource) ---
    Route::apiResources([
        'rols' => RolController::class,
        'users' => UserController::class,
        'pacientes' => PacienteController::class,
        'internaciones' => InternacionController::class,
        'medicamentos' => MedicamentoController::class,
        'signos' => SignoController::class,
        'hospitals' => HospitalController::class,
        'tratamientos' => TratamientoController::class,
        'controls' => ControlController::class,
        'especialidades' => EspecialidadController::class,
        'salas' => SalaController::class,
        'camas' => CamaController::class,
        'ocupaciones' => OcupacionController::class,
        'recetas' => RecetaController::class,
        'administraciones' => AdministraController::class,
        'cuidados-aplicados' => CuidadoAplicadoController::class,
        'permissions' => PermissionController::class,
        'medicamento-categorias' => MedicamentoCategoriaController::class,

        // --- AÑADIR ESTAS TRES LÍNEAS AQUÍ ---
        'tipos-dieta' => TipoDietaController::class,
        'alimentaciones' => AlimentacionController::class,
        'consumos' => ConsumeController::class,
    ]);
});
