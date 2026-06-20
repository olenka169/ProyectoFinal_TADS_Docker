@extends('adminlte::page')

@section('title', 'Feriados')

@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)

@section('content')

    <div class="pt-3"></div>

    <div class="card">
        <div class="card-header">
            <button type="button" class="btn btn-primary btn-sm float-right" id="btn-nuevo">
                <i class="fas fa-plus"></i> Nuevo Feriado
            </button>

            <h4>
                <i class="fas fa-flag"></i>
                Lista de Feriados
            </h4>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card shadow-sm mb-0">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-alt text-primary"></i>
                    <h5 class="mb-0 mt-1" id="totalHolidays">0</h5>
                    <small class="text-muted">Total Feriados</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm mb-0">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle text-success"></i>
                    <h5 class="mb-0 mt-1" id="activeHolidays">0</h5>
                    <small class="text-muted">Feriados Activos</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm mb-0">
                <div class="card-body text-center">
                    <i class="fas fa-hourglass-half text-warning"></i>
                    <h5 class="mb-0 mt-1" id="upcomingHolidays">0</h5>
                    <small class="text-muted">Próximos Feriados</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm mb-0">
                <div class="card-body text-center">
                    <i class="fas fa-calendar text-info"></i>
                    <h5 class="mb-0 mt-1">{{ date('Y') }}</h5>
                    <small class="text-muted">Año Actual</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body border-bottom">
            <div class="row align-items-end">

                <div class="col-md-3">
                    <div class="form-group mb-0">
                        <label>Fecha de inicio</label>
                        <input type="date" id="start_date" class="form-control">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group mb-0">
                        <label>Fecha de fin</label>
                        <input type="date" id="end_date" class="form-control">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group mb-0">
                        <label>Estado</label>
                        <select id="status" class="form-control">
                            <option value="">Todos</option>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group mb-0">
                        <label class="d-none d-md-block">&nbsp;</label>

                        <div class="d-flex">
                            <button type="button" id="btn-filtrar" class="btn btn-primary btn-sm flex-fill mr-1">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>

                            <button type="button" id="btn-limpiar" class="btn btn-secondary btn-sm flex-fill ml-1">
                                <i class="fas fa-eraser"></i> Limpiar
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-striped table-hover table-sm text-nowrap" id="datatable">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Descripción</th>
                        <th>Día</th>
                        <th>Estado</th>
                        <th>Creación</th>
                        <th width="120">Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="modal fade" id="FormModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        Formulario de Feriado
                    </h5>

                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body"></div>
            </div>
        </div>
    </div>

@stop

@section('js')

    <script>
        let table;

        $(document).ready(function() {
            table = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                autoWidth: false,
                order: [
                    [0, 'asc']
                ],
                ajax: {
                    url: "{{ route('admin.holidays.index') }}",
                    data: function(d) {
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.status = $('#status').val();
                    }
                },
                columns: [{
                        data: "date",
                        name: "holidays.date"
                    },
                    {
                        data: "description",
                        name: "holidays.description"
                    },
                    {
                        data: "day",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: "status_badge",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: "created_at",
                        name: "holidays.created_at"
                    },
                    {
                        data: "actions",
                        orderable: false,
                        searchable: false
                    }
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json'
                },
                drawCallback: function() {
                    updateHolidayStats();
                }
            });

            updateHolidayStats();

            $('#btn-filtrar').click(function() {
                table.ajax.reload(null, false);
                updateHolidayStats();
            });

            $('#btn-limpiar').click(function() {
                $('#start_date').val('');
                $('#end_date').val('');
                $('#status').val('');

                table.ajax.reload(null, false);
                updateHolidayStats();
            });
        });

        function updateHolidayStats() {
            $.get("{{ route('admin.holidays.stats') }}", {
                start_date: $('#start_date').val(),
                end_date: $('#end_date').val(),
                status: $('#status').val()
            }, function(response) {
                $('#totalHolidays').text(response.total);
                $('#activeHolidays').text(response.active);
                $('#upcomingHolidays').text(response.upcoming);
            });
        }

        $('#btn-nuevo').click(function() {
            $.ajax({
                url: "{{ route('admin.holidays.create') }}",
                type: "GET",
                success: function(response) {
                    $('#FormModal .modal-title')
                        .html('<i class="fas fa-flag"></i> Nuevo Feriado');

                    $('#FormModal .modal-body')
                        .html(response);

                    $('#FormModal').modal("show");

                    $('#FormModal form').on("submit", function(e) {
                        e.preventDefault();

                        if (!this.checkValidity()) {
                            this.reportValidity();
                            return;
                        }

                        enviarFormulario(this);
                    });
                }
            });
        });

        $(document).on('click', '.btn-editar', function() {
            let id = $(this).attr("id");

            $.ajax({
                url: "{{ route('admin.holidays.edit', ':id') }}".replace(':id', id),
                type: "GET",
                success: function(response) {
                    $('#FormModal .modal-title')
                        .html('<i class="fas fa-pen"></i> Modificar Feriado');

                    $('#FormModal .modal-body')
                        .html(response);

                    $('#FormModal').modal("show");

                    $('#FormModal form').on("submit", function(e) {
                        e.preventDefault();

                        if (!this.checkValidity()) {
                            this.reportValidity();
                            return;
                        }

                        enviarFormulario(this);
                    });
                }
            });
        });

        function enviarFormulario(formulario) {
            let form = $(formulario);
            let formData = new FormData(formulario);

            $.ajax({
                url: form.attr('action'),
                type: form.attr('method'),
                data: formData,
                processData: false,
                contentType: false,

                success: function(response) {
                    $('#FormModal').modal("hide");
                    refreshTable();

                    Swal.fire(
                        'Proceso exitoso',
                        response.message,
                        'success'
                    );
                },

                error: function(xhr) {
                    let response = xhr.responseJSON;

                    Swal.fire(
                        'Ocurrió un error',
                        response ? response.message : 'No se pudo completar la operación',
                        'error'
                    );
                }
            });
        }

        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault();

            let url = $(this).data('url');

            Swal.fire({
                title: "¿Está seguro de eliminar?",
                text: "Esta acción es irreversible",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed || result.value) {
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },

                        success: function(response) {
                            refreshTable();

                            Swal.fire(
                                'Proceso exitoso',
                                response.message,
                                'success'
                            );
                        },

                        error: function(xhr) {
                            let response = xhr.responseJSON;

                            Swal.fire(
                                'Ocurrió un error',
                                response ? response.message : 'No se pudo eliminar',
                                'error'
                            );
                        }
                    });
                }
            });
        });

        function refreshTable() {
            $('#datatable').DataTable().ajax.reload(null, false);
            updateHolidayStats();
        }

        $('#FormModal').on('hidden.bs.modal', function() {
            $('#FormModal .modal-body').html('');
        });
    </script>

@stop
