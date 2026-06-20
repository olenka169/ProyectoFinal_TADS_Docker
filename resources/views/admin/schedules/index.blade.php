@extends('adminlte::page')

@section('title', 'Programaciones')

@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)
@section('plugins.Select2', true)

@section('content')
<div class="pt-4"></div>

<div class="card">
    <div class="card-header">
        <div class="float-right">
            <button type="button" class="btn btn-primary btn-sm" id="btn-nueva-programacion">
                <i class="fas fa-plus"></i> Nueva Programación
            </button>
            <button type="button" class="btn btn-info btn-sm" id="btn-programacion-masiva">
                <i class="fas fa-layer-group"></i> Programación Masiva
            </button>
        </div>
        <h4><i class="fas fa-calendar-check"></i> Lista de Programaciones</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="schedules-table" class="table table-bordered table-striped w-100">
                <thead>
                    <tr>
                        <th>Grupo</th>
                        <th>Zona</th>
                        <th>Turno</th>
                        <th>Vehículo</th>
                        <th>Conductor</th>
                        <th>Ayudantes</th>
                        <th>Periodo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Modal para Nueva Programación -->
<div class="modal fade" id="modal-schedule" tabindex="-1" role="dialog" aria-labelledby="modalScheduleLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalScheduleLabel"><i class="fas fa-calendar-plus"></i> Nueva Programación</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="schedule-form">
                @csrf
                <input type="hidden" name="id" id="schedule_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Grupo de Personal (Plantilla)</label>
                                <select name="personnel_group_id" id="personnel_group_id" class="form-control select2" style="width: 100%" required>
                                    <option value="">Seleccione un grupo...</option>
                                    @foreach($groups as $group)
                                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Fecha Inicio</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Fecha Fin</label>
                                <input type="date" name="end_date" id="end_date" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Zona</label>
                                <select name="zone_id" id="zone_id" class="form-control select2" style="width: 100%">
                                    @foreach($zones as $zone)
                                        <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Turno</label>
                                <select name="shift_id" id="shift_id" class="form-control select2" style="width: 100%">
                                    @foreach($shifts as $shift)
                                        <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Vehículo</label>
                                <select name="vehicle_id" id="vehicle_id" class="form-control select2" style="width: 100%">
                                    @foreach($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}">{{ $vehicle->plate }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Días de la Semana</label>
                                <div class="d-flex flex-wrap border p-1 rounded bg-white">
                                    @foreach(['Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá', 'Do'] as $day)
                                        <div class="custom-control custom-checkbox mr-2">
                                            <input class="custom-control-input workday-checkbox" type="checkbox" name="workdays[]" id="day_{{ $day }}" value="{{ $day }}">
                                            <label for="day_{{ $day }}" class="custom-control-label font-weight-normal">{{ $day }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-4 text-center">
                            <label class="font-weight-bold text-muted small text-uppercase">Conductor</label>
                            <input type="hidden" name="driver_id" id="driver_id">
                            <div id="driver-card">
                                <div class="p-3 border rounded bg-light text-muted small">Sin asignar</div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <label class="font-weight-bold text-muted small text-uppercase">Ayudante 1</label>
                            <input type="hidden" name="helper_ids[]" id="helper_id_1">
                            <div id="helper-card-1">
                                <div class="p-3 border rounded bg-light text-muted small">Sin asignar</div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <label class="font-weight-bold text-muted small text-uppercase">Ayudante 2</label>
                            <input type="hidden" name="helper_ids[]" id="helper_id_2">
                            <div id="helper-card-2">
                                <div class="p-3 border rounded bg-light text-muted small">Sin asignar</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Notas / Observaciones</label>
                        <textarea name="notes" id="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" id="btn-validate" class="btn btn-info">
                        <i class="fas fa-shield-alt"></i> Validar Disponibilidad
                    </button>
                    <button type="submit" id="btn-save" class="btn btn-success" disabled>
                        <i class="fas fa-save"></i> Guardar Programación
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Programación Masiva -->
<div class="modal fade" id="modal-mass" tabindex="-1" role="dialog" aria-labelledby="modalMassLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalMassLabel"><i class="fas fa-layer-group"></i> Nueva Programación Masiva</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Fecha Inicio</label>
                            <input type="date" id="mass_start_date" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Fecha Fin</label>
                            <input type="date" id="mass_end_date" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Filtrar por Turno</label>
                            <select id="mass_shift_id" class="form-control select2" style="width: 100%">
                                <option value="">Todos los turnos</option>
                                @foreach($shifts as $shift)
                                    <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div id="feriados-alerta" class="alert alert-warning d-none">
                    <i class="fas fa-calendar-day"></i> Feriados detectados en el rango: <span id="feriados-lista"></span>
                </div>

                <div class="text-center mb-3">
                    <button type="button" class="btn btn-primary" id="btn-previsualizar-masiva">
                        <i class="fas fa-search"></i> Previsualizar Grupos
                    </button>
                </div>

                <div id="previsualizacion-container" class="d-none">
                    <hr>
                    <h5><i class="fas fa-list"></i> Grupos a Programar</h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover">
                            <thead class="bg-light text-center">
                                <tr>
                                    <th width="40"><input type="checkbox" id="mass-select-all"></th>
                                    <th>Grupo de Personal</th>
                                    <th>Configuración (Turno/Vehículo)</th>
                                    <th>Asignación de Personal</th>
                                    <th>Validación / Avisos</th>
                                </tr>
                            </thead>
                            <tbody id="mass-preview-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success d-none" id="btn-confirmar-masiva">
                    <i class="fas fa-check-circle"></i> Confirmar y Registrar Programación
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Modificar Programación (Rediseñado según 4ta Imagen) -->
<div class="modal fade" id="modal-edit-schedule" tabindex="-1" role="dialog" aria-labelledby="modalEditScheduleLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content border-warning">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalEditScheduleLabel"><i class="fas fa-edit"></i> Modificar Programación de Servicio</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="edit-schedule-form">
                @csrf
                <input type="hidden" name="schedule_id" id="edit_schedule_id">
                <div class="modal-body">
                    <div class="row">
                        <!-- Columna Turnos -->
                        <div class="col-md-4">
                            <div class="card card-outline card-primary h-100">
                                <div class="card-header">
                                    <h3 class="card-title text-primary font-weight-bold">TURNOS</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-4">
                                        <label class="text-muted small">Turno Actual:</label>
                                        <div id="current_shift_display" class="font-weight-bold h5 text-dark border-bottom pb-2">-</div>
                                    </div>
                                    <div class="form-group">
                                        <label>Cambiar Turno a:</label>
                                        <select name="shift_id" id="edit_shift_id" class="form-control select2" style="width: 100%">
                                            @foreach($shifts as $shift)
                                                <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Días Programados</label>
                                        <div class="d-flex flex-wrap border p-2 rounded bg-light" id="edit-workdays-container">
                                            <!-- Se llena vía JS -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna Vehículo -->
                        <div class="col-md-4">
                            <div class="card card-outline card-success h-100">
                                <div class="card-header">
                                    <h3 class="card-title text-success font-weight-bold">VEHÍCULO</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-4">
                                        <label class="text-muted small">Vehículo Actual:</label>
                                        <div id="current_vehicle_display" class="font-weight-bold h5 text-dark border-bottom pb-2">-</div>
                                    </div>
                                    <div class="form-group">
                                        <label>Cambiar Vehículo a:</label>
                                        <select name="vehicle_id" id="edit_vehicle_id" class="form-control select2" style="width: 100%">
                                            @foreach($vehicles as $vehicle)
                                                <option value="{{ $vehicle->id }}">{{ $vehicle->plate }} - {{ $vehicle->brand?->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div id="edit-vehicle-preview" class="mt-2 p-3 text-center">
                                        <i class="fas fa-truck fa-3x text-muted"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna Personal (Solo Permanente/Nombrado con Asistencia) -->
                        <div class="col-md-4">
                            <div class="card card-outline card-info h-100">
                                <div class="card-header">
                                    <h3 class="card-title text-info font-weight-bold">PERSONAL</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Conductor (Permanente/Nombrado)</label>
                                        <select name="driver_id" id="edit_driver_id" class="form-control select2-eligible" style="width: 100%"></select>
                                    </div>
                                    <div class="form-group">
                                        <label>Ayudante 1</label>
                                        <select name="helper_ids[]" id="edit_helper_1" class="form-control select2-eligible" style="width: 100%"></select>
                                    </div>
                                    <div class="form-group">
                                        <label>Ayudante 2</label>
                                        <select name="helper_ids[]" id="edit_helper_2" class="form-control select2-eligible" style="width: 100%"></select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-3">
                        <label class="text-danger font-weight-bold">Motivo de la Modificación (Requerido)</label>
                        <textarea name="reason" id="edit_reason" class="form-control" rows="2" placeholder="Escriba aquí el motivo del cambio..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="submit" class="btn btn-warning font-weight-bold">
                        <i class="fas fa-save"></i> Aplicar Modificaciones
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Detalle Diario -->
<div class="modal fade" id="modal-daily" tabindex="-1" role="dialog" aria-labelledby="modalDailyLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalDailyLabel"><i class="fas fa-calendar-day"></i> Seguimiento de Programación Diaria</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Turno</th>
                                <th>Vehículo</th>
                                <th>Conductor</th>
                                <th>Ayudantes</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="daily-tbody">
                            <!-- Se llena vía AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@stop

@section('js')
<script>
$(function() {
    let table = $('#schedules-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: '{{ route("admin.schedules.index") }}',
        columns: [
            { data: 'group_name', name: 'group_name' },
            { data: 'zone_name', name: 'zone_name' },
            { data: 'shift_name', name: 'shift_name' },
            { data: 'vehicle_plate', name: 'vehicle_plate' },
            { data: 'driver_name', name: 'driver_name' },
            { data: 'helpers_names', name: 'helpers_names' },
            { data: 'date_range', name: 'date_range' },
            { data: 'status_badge', name: 'status_badge' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
        }
    });

    $(document).on('click', '.btn-daily', function() {
        let id = $(this).data('id');
        $('#modal-daily').attr('data-active-id', id);
        
        Swal.fire({
            title: 'Cargando detalle...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.get(`/admin/schedules/${id}`, function(schedule) {
            Swal.close();
            let html = '';
            
            if (schedule.dailies && schedule.dailies.length > 0) {
                schedule.dailies.forEach(d => {
                    let helpers = d.helpers.map(h => h.names + ' ' + h.lastnames).join('<br>');
                    let statusBadge = '';
                    if (d.status == 'pendiente') statusBadge = '<span class="badge badge-secondary">Pendiente</span>';
                    else if (d.status == 'completado') statusBadge = '<span class="badge badge-success">Completado</span>';
                    else if (d.status == 'reprogramado') statusBadge = '<span class="badge badge-warning">Reprogramado</span>';
                    else if (d.status == 'cancelado') statusBadge = '<span class="badge badge-danger">Cancelado</span>';
                    else statusBadge = `<span class="badge badge-info">${d.status}</span>`;

                    let dateStr = d.date; 
                    if (typeof dateStr === 'string' && dateStr.includes('T')) {
                        dateStr = dateStr.split('T')[0];
                    }
                    let parts = dateStr.split('-');
                    let formattedDate = `${parts[2]}/${parts[1]}/${parts[0]}`;

                    html += `
                        <tr>
                            <td class="font-weight-bold">${formattedDate}</td>
                            <td>${d.shift ? d.shift.name : 'N/A'}</td>
                            <td>${d.vehicle ? d.vehicle.plate : 'N/A'}</td>
                            <td>${d.driver ? d.driver.names + ' ' + d.driver.lastnames : 'N/A'}</td>
                            <td><small>${helpers || 'Sin ayudantes'}</small></td>
                            <td class="text-center">${statusBadge}</td>
                            <td>
                                <button class="btn btn-xs btn-primary btn-change-status" data-id="${d.id}" data-status="${d.status}" title="Cambiar Estado"><i class="fas fa-sync"></i></button>
                            </td>
                        </tr>
                    `;
                });
            } else {
                html = '<tr><td colspan="7" class="text-center">No hay registros diarios para esta programación.</td></tr>';
            }

            $('#daily-tbody').html(html);
            $('#modal-daily').modal('show');
        }).fail(function() {
            Swal.close();
            Swal.fire('Error', 'No se pudo cargar el detalle diario.', 'error');
        });
    });

    $(document).on('click', '.btn-change-status', function() {
        let id = $(this).data('id');
        let currentStatus = $(this).data('status');
        
        Swal.fire({
            title: 'Cambiar Estado',
            input: 'select',
            inputOptions: {
                'pendiente': 'Pendiente',
                'completado': 'Completado',
                'cancelado': 'Cancelado'
            },
            inputValue: currentStatus,
            showCancelButton: true,
            confirmButtonText: 'Actualizar',
            cancelButtonText: 'Cerrar'
        }).then((result) => {
            if (result.value) {
                $.post(`/admin/schedules/daily/${id}/status`, {
                    _token: '{{ csrf_token() }}',
                    status: result.value
                }, function() {
                    Swal.fire('Actualizado', 'El estado se ha actualizado correctamente.', 'success');
                    let activeId = $('#modal-daily').attr('data-active-id');
                    if (activeId) {
                        $('.btn-daily[data-id="' + activeId + '"]').click();
                    }
                });
            }
        });
    });

    $(document).on('click', '.btn-edit', function() {
        let id = $(this).data('id');
        
        Swal.fire({
            title: 'Cargando datos de edición...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.get(`/admin/schedules/${id}/edit`, function(response) {
            Swal.close();
            let s = response.schedule;
            let eligibleDrivers = response.eligibleDrivers;
            let eligibleHelpers = response.eligibleHelpers;
            let currentDriver = response.current_driver;
            let currentHelpers = response.current_helpers;

            // Limpiar y configurar modal de edición
            $('#edit_schedule_id').val(s.id);
            $('#edit_reason').val('');
            
            // Visualización de Datos Actuales
            $('#current_shift_display').text(response.current_shift_name || 'No definido');
            $('#current_vehicle_display').text(response.current_vehicle_plate || 'No definido');

            // Configurar Combos de Cambio
            $('#edit_shift_id').val(s.shift_id).trigger('change');
            $('#edit_vehicle_id').val(s.vehicle_id).trigger('change');

            // --- Lógica de CONDUCTORES ---
            let driverOptions = '<option value="">Seleccione conductor...</option>';
            let addedDriverIds = new Set();

            // 1. Agregar explícitamente al Conductor Actual (Independientemente de filtros)
            if (currentDriver) {
                driverOptions += `<option value="${currentDriver.id}">${currentDriver.names} ${currentDriver.lastnames} (ACTUAL)</option>`;
                addedDriverIds.add(currentDriver.id);
            }

            // 2. Agregar conductores elegibles (sin grupo, contrato ok, asistencia ok)
            eligibleDrivers.forEach(p => {
                if (!addedDriverIds.has(p.id)) {
                    driverOptions += `<option value="${p.id}">${p.names} ${p.lastnames}</option>`;
                    addedDriverIds.add(p.id);
                }
            });
            $('#edit_driver_id').html(driverOptions).val(s.driver_id).trigger('change');

            // --- Lógica de AYUDANTES ---
            let helperOptions = '<option value="">Seleccione ayudante...</option>';
            let addedHelperIds = new Set();

            // 1. Agregar explícitamente a los Ayudantes Actuales (Independientemente de filtros)
            if (currentHelpers && currentHelpers.length > 0) {
                currentHelpers.forEach(h => {
                    if (!addedHelperIds.has(h.id)) {
                        helperOptions += `<option value="${h.id}">${h.names} ${h.lastnames} (ACTUAL)</option>`;
                        addedHelperIds.add(h.id);
                    }
                });
            }

            // 2. Agregar ayudantes elegibles (sin grupo, contrato ok, asistencia ok)
            eligibleHelpers.forEach(p => {
                if (!addedHelperIds.has(p.id)) {
                    helperOptions += `<option value="${p.id}">${p.names} ${p.lastnames}</option>`;
                    addedHelperIds.add(p.id);
                }
            });

            $('#edit_helper_1').html(helperOptions);
            $('#edit_helper_2').html(helperOptions);
            
            if (s.helpers && s.helpers.length > 0) $('#edit_helper_1').val(s.helpers[0].id).trigger('change');
            if (s.helpers && s.helpers.length > 1) $('#edit_helper_2').val(s.helpers[1].id).trigger('change');

            // Mostrar días programados
            let daysHtml = '';
            if (s.workdays) {
                s.workdays.forEach(wd => {
                    daysHtml += `<span class="badge badge-info m-1">${wd.day}</span>`;
                });
            }
            $('#edit-workdays-container').html(daysHtml || '<span class="text-muted small">No definidos</span>');

            $('#modal-edit-schedule').modal('show');
        }).fail(function() {
            Swal.close();
            Swal.fire('Error', 'No se pudieron cargar los datos para modificar.', 'error');
        });
    });

    $('#edit-schedule-form').on('submit', function(e) {
        e.preventDefault();
        let id = $('#edit_schedule_id').val();
        
        $.ajax({
            url: `/admin/schedules/${id}`,
            method: 'PUT',
            data: $(this).serialize(),
            success: function() {
                $('#modal-edit-schedule').modal('hide');
                table.ajax.reload();
                Swal.fire('Actualizado', 'La programación se ha modificado y marcado como Reprogramada.', 'success');
            }
        });
    });

    // --- PROGRAMACION MASIVA ---
    let massPreviewData = [];

    $('#btn-programacion-masiva').on('click', function() {
        $('#mass-preview-tbody').empty();
        $('#previsualizacion-container').addClass('d-none');
        $('#btn-confirmar-masiva').addClass('d-none');
        $('#feriados-alerta').addClass('d-none');
        $('#modal-mass').modal('show');
    });

    $('#btn-previsualizar-masiva').on('click', function() {
        let start = $('#mass_start_date').val();
        let end = $('#mass_end_date').val();
        let shift = $('#mass_shift_id').val();

        if (!start || !end) {
            Swal.fire('Atención', 'Seleccione un rango de fechas.', 'warning');
            return;
        }

        Swal.fire({ title: 'Analizando...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }});

        $.post('{{ route("admin.schedules.preview-mass") }}', {
            _token: '{{ csrf_token() }}',
            start_date: start,
            end_date: end,
            shift_id: shift
        }, function(response) {
            Swal.close();
            
            if (response.holidays.length > 0) {
                let fList = response.holidays.map(h => `${h.description} (${new Date(h.date + 'T00:00:00').toLocaleDateString()})`).join(', ');
                $('#feriados-lista').text(fList);
                $('#feriados-alerta').removeClass('d-none');
            } else {
                $('#feriados-alerta').addClass('d-none');
            }

            let html = '';
            massPreviewData = response.preview;

            response.preview.forEach((item, index) => {
                let group = item.group;
                let avail = item.availability;
                let statusBadge = avail.valid ? '<span class="badge badge-success"><i class="fas fa-check"></i> Válido</span>' : '<span class="badge badge-danger"><i class="fas fa-times"></i> Conflicto</span>';
                let alertClass = avail.valid ? '' : 'table-warning';
                let messages = '';
                
                if (avail.errors.length > 0) {
                    messages = `<div class="text-danger small font-weight-bold mt-1">${avail.errors.map(e => `• ${e}`).join('<br>')}</div>`;
                }

                let personnelOptions = '<option value="">Seleccione...</option>';
                response.personnels.forEach(p => {
                    personnelOptions += `<option value="${p.id}">${p.names} ${p.lastnames}</option>`;
                });

                html += `
                    <tr data-index="${index}" class="${alertClass}">
                        <td class="text-center align-middle">
                            <input type="checkbox" class="group-select-checkbox" data-index="${index}">
                        </td>
                        <td class="align-middle">
                            <div class="font-weight-bold">${group.name}</div>
                            <div class="text-muted small">${group.zone.name}</div>
                        </td>
                        <td class="align-middle text-center">
                            <span class="badge badge-light border d-block mb-1">${group.shift.name}</span>
                            <span class="small text-muted"><i class="fas fa-truck"></i> ${group.vehicle.plate}</span>
                        </td>
                        <td>
                            <div class="input-group input-group-sm mb-1">
                                <div class="input-group-prepend"><span class="input-group-text bg-light border-0"><i class="fas fa-user-tie"></i></span></div>
                                <select class="form-control select2-mass driver-select" data-index="${index}">${personnelOptions}</select>
                            </div>
                            <div class="input-group input-group-sm mb-1">
                                <div class="input-group-prepend"><span class="input-group-text bg-light border-0"><i class="fas fa-user-friends"></i></span></div>
                                <select class="form-control select2-mass helper1-select" data-index="${index}">${personnelOptions}</select>
                            </div>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend"><span class="input-group-text bg-light border-0"><i class="fas fa-user-friends"></i></span></div>
                                <select class="form-control select2-mass helper2-select" data-index="${index}">${personnelOptions}</select>
                            </div>
                        </td>
                        <td class="align-middle">${statusBadge}${messages}</td>
                    </tr>
                `;
            });

            $('#mass-preview-tbody').html(html);
            $('#mass-select-all').on('change', function() {
                $('.group-select-checkbox').prop('checked', $(this).is(':checked'));
            });
            $('.select2-mass').select2({ theme: 'bootstrap4', dropdownParent: $('#modal-mass') });
            
            response.preview.forEach((item, index) => {
                let tr = $(`tr[data-index="${index}"]`);
                tr.find('.driver-select').val(item.group.driver_id).trigger('change');
                if (item.group.helpers[0]) tr.find('.helper1-select').val(item.group.helpers[0].personnel_id).trigger('change');
                if (item.group.helpers[1]) tr.find('.helper2-select').val(item.group.helpers[1].personnel_id).trigger('change');
            });

            $('#previsualizacion-container').removeClass('d-none');
            $('#btn-confirmar-masiva').removeClass('d-none');
        });
    });

    $('#btn-confirmar-masiva').on('click', function() {
        let groupsToStore = [];
        let hasErrors = false;
        let selectedCount = 0;

        $('#mass-preview-tbody tr').each(function() {
            if ($(this).find('.group-select-checkbox').is(':checked')) {
                selectedCount++;
                let index = $(this).data('index');
                let item = massPreviewData[index];
                if ($(this).hasClass('table-warning')) hasErrors = true;

                groupsToStore.push({
                    group_id: item.group.id,
                    zone_id: item.group.zone_id,
                    shift_id: item.group.shift_id,
                    vehicle_id: item.group.vehicle_id,
                    driver_id: $(this).find('.driver-select').val(),
                    helper_ids: [
                        $(this).find('.helper1-select').val(),
                        $(this).find('.helper2-select').val()
                    ].filter(id => id !== "")
                });
            }
        });

        if (selectedCount === 0) {
            Swal.fire('Atención', 'Debe seleccionar al menos un grupo de la lista.', 'warning');
            return;
        }

        if (hasErrors) {
            Swal.fire({
                title: '¿Continuar?',
                text: 'Ha seleccionado grupos con avisos. ¿Desea registrarlos?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, registrar'
            }).then((result) => {
                if (result.value) executeMassStore(groupsToStore);
            });
        } else {
            executeMassStore(groupsToStore);
        }
    });

    function executeMassStore(groups) {
        Swal.fire({ title: 'Registrando...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }});
        $.post('{{ route("admin.schedules.store-mass") }}', {
            _token: '{{ csrf_token() }}',
            start_date: $('#mass_start_date').val(),
            end_date: $('#mass_end_date').val(),
            groups: groups
        }, function() {
            Swal.close();
            $('#modal-mass').modal('hide');
            table.ajax.reload();
            Swal.fire('¡Éxito!', 'Programaciones registradas.', 'success');
        });
    }

    function createPersonnelCard(id, name, type) {
        if (!id) return '';
        return `
            <div class="card card-outline card-secondary mb-2 shadow-sm animate__animated animate__fadeIn">
                <div class="card-body p-2">
                    <div class="row align-items-center">
                        <div class="col-auto"><div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;"><i class="fas fa-user text-white"></i></div></div>
                        <div class="col"><h6 class="mb-0 font-weight-bold">${name}</h6><small class="text-muted text-uppercase small">${type}</small></div>
                    </div>
                </div>
            </div>
        `;
    }

    function updatePersonnelCards(group) {
        let form = $('#schedule-form');
        if (!group) {
            form.find('#driver_id').val('');
            form.find('#helper_id_1').val('');
            form.find('#helper_id_2').val('');
            form.find('#driver-card').html('<div class="p-3 border rounded bg-light text-muted small text-center">Sin asignar</div>');
            form.find('#helper-card-1').html('<div class="p-3 border rounded bg-light text-muted small text-center">Sin asignar</div>');
            form.find('#helper-card-2').html('<div class="p-3 border rounded bg-light text-muted small text-center">Sin asignar</div>');
            return;
        }
        if (group.driver) {
            form.find('#driver_id').val(group.driver.id);
            form.find('#driver-card').html(createPersonnelCard(group.driver.id, `${group.driver.names} ${group.driver.lastnames}`, 'Conductor'));
        }
        if (group.helpers && group.helpers.length > 0) {
            let h1 = group.helpers[0].personnel;
            if (h1) {
                form.find('#helper_id_1').val(h1.id);
                form.find('#helper-card-1').html(createPersonnelCard(h1.id, `${h1.names} ${h1.lastnames}`, 'Ayudante 1'));
            }
        } else {
            form.find('#helper-card-1').html('<div class="p-3 border rounded bg-light text-muted small text-center">Sin asignar</div>');
        }
        if (group.helpers && group.helpers.length > 1) {
            let h2 = group.helpers[1].personnel;
            if (h2) {
                form.find('#helper_id_2').val(h2.id);
                form.find('#helper-card-2').html(createPersonnelCard(h2.id, `${h2.names} ${h2.lastnames}`, 'Ayudante 2'));
            }
        } else {
            form.find('#helper-card-2').html('<div class="p-3 border rounded bg-light text-muted small text-center">Sin asignar</div>');
        }
    }

    $('#btn-nueva-programacion').on('click', function() {
        $('#schedule-form')[0].reset();
        $('#schedule_id').val('');
        $('.select2').val('').trigger('change');
        $('.workday-checkbox').prop('checked', false);
        updatePersonnelCards(null);
        $('#btn-save').prop('disabled', true);
        $('#modalScheduleLabel').html('<i class="fas fa-calendar-plus"></i> Nueva Programación');
        $('#modal-schedule').modal('show');
    });

    $('#personnel_group_id').on('change', function() {
        let groupId = $(this).val();
        if (!groupId || $('#schedule_id').val()) return;
        $.get("{{ route('admin.personnel-groups.index') }}/" + groupId, function(group) {
            let form = $('#schedule-form');
            form.find('#zone_id').val(group.zone_id).trigger('change');
            form.find('#shift_id').val(group.shift_id).trigger('change');
            form.find('#vehicle_id').val(group.vehicle_id).trigger('change');
            form.find('.workday-checkbox').prop('checked', false);
            if (group.workdays) {
                group.workdays.forEach(wd => {
                    let shortDay = wd.day.substring(0, 2);
                    if (wd.day == 'Miércoles') shortDay = 'Mi';
                    if (wd.day == 'Sábado') shortDay = 'Sá';
                    if (wd.day == 'Domingo') shortDay = 'Do';
                    form.find(`.workday-checkbox[value="${shortDay}"]`).prop('checked', true);
                });
            }
            $('#btn-save').prop('disabled', true);
            updatePersonnelCards(group);
        });
    });

    $('input, select').on('change', function() { $('#btn-save').prop('disabled', true); });

    $('#btn-validate').on('click', function() {
        let formData = $('#schedule-form').serialize();
        Swal.fire({ title: 'Validando...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }});
        $.ajax({
            url: '{{ route("admin.schedules.validate-availability") }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                Swal.close();
                if (!response.errors || response.errors.length === 0) {
                    Swal.fire('¡Éxito!', 'La programación es válida.', 'success');
                    $('#btn-save').prop('disabled', false);
                } else {
                    Swal.fire('Conflicto', response.errors.join('<br>'), 'warning');
                    $('#btn-save').prop('disabled', true);
                }
            }
        });
    });

    $('#schedule-form').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '{{ route("admin.schedules.store") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function() {
                $('#modal-schedule').modal('hide');
                table.ajax.reload();
                Swal.fire('Guardado', 'Registrado con éxito.', 'success');
            }
        });
    });

    $(document).on('click', '.btn-delete', function() {
        let id = $(this).data('id');
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede deshacer.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.value) {
                $.ajax({
                    url: `{{ route("admin.schedules.index") }}/${id}`,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function() {
                        table.ajax.reload();
                        Swal.fire('Eliminado', 'La programación ha sido eliminada.', 'success');
                    }
                });
            }
        });
    });

    $('.select2').select2({ theme: 'bootstrap4' });
});
</script>
@stop
