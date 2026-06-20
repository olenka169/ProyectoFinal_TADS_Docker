@extends('adminlte::page')

@section('title', 'Panel Principal')

@section('content_header')
    <h1 class="font-weight-bold"></h1>
@stop

@section('content')
    <div class="card shadow border-0 mb-4" style="border-radius: 18px;">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="font-weight-bold text-primary">
                        Sistema de Gestión de Residuos Sólidos Urbanos
                    </h2>
                    <p class="text-muted mb-2">
                        Municipalidad Distrital de José Leonardo Ortiz
                    </p>
                    <p>
                        Plataforma institucional orientada a la administración de vehículos,
                        personal, programación y control de actividades relacionadas con la
                        recolección de residuos sólidos urbanos.
                    </p>
                </div>

                <div class="col-md-4 text-center">
                    <img src="{{ asset('img/logo-jlo.png') }}" style="max-width: 230px;">
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h4>Vehículos</h4>
                    <p>Colores, marcas, modelos, tipos y vehículos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-truck"></i>
                </div>
                <a href="{{ route('admin.vehicles.index') }}" class="small-box-footer">
                    Acceder <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h4>Personal</h4>
                    <p>Tipos, personal, contratos, asistencias y vacaciones</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('admin.personnels.index') }}" class="small-box-footer">
                    Acceder <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h4>Programación</h4>
                    <p>Turnos, zonas, feriados, grupos de personal y programación</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <a href="{{ route('admin.schedules.index') }}" class="small-box-footer">
                    Acceder <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h4>Cambios</h4>
                    <p>Control y seguimiento operativo, solicitudes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <a href="#" class="small-box-footer">
                    Próximamente <i class="fas fa-clock"></i>
                </a>
            </div>
        </div>
    </div>
@stop
