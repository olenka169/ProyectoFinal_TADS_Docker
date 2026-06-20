@extends('adminlte::page')

@section('title', 'Asistencias')

@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)
@section('plugins.Select2', true)

@section('content')

    <div class="pt-3"></div>

    <div class="card">
        <div class="card-header">
            <button type="button" class="btn btn-primary btn-sm float-right" id="btn-nuevo">
                <i class="fas fa-plus"></i> Nueva Asistencia
            </button>

            <h4>
                <i class="fas fa-clipboard-check"></i>
                Lista de Asistencias
            </h4>
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

                <div class="col-md-4">
                    <div class="form-group mb-0">
                        <label>Buscar personal</label>
                        <input type="text" id="personnel_search" class="form-control"
                            placeholder="Ingrese DNI, nombre o apellido">
                    </div>
                </div>

                <div class="col-md-2">
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
                        <th>DNI</th>
                        <th>Personal</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Turno</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Notas</th>
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
                    <h5 class="modal-title">Formulario de Asistencia</h5>

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
        $(document).ready(function() {
            $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                autoWidth: false,
                order: [
                    [2, 'desc'],
                    [3, 'desc']
                ],
                ajax: {
                    url: "{{ route('admin.attendances.index') }}",
                    data: function(d) {
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.personnel_search = $('#personnel_search').val();
                    }
                },
                columns: [{
                        data: "personnel_dni",
                        name: "personnels.dni"
                    },
                    {
                        data: "personnel_name",
                        name: "personnels.names"
                    },
                    {
                        data: "date",
                        name: "attendances.date"
                    },
                    {
                        data: "time",
                        name: "attendances.time"
                    },
                    {
                        data: "shift_name",
                        name: "shifts.name"
                    },
                    {
                        data: "type_badge",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: "status_badge",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: "notes",
                        name: "attendances.notes"
                    },
                    {
                        data: "actions",
                        orderable: false,
                        searchable: false
                    }
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json'
                }
            });

            $('#btn-filtrar').click(function() {
                $('#datatable').DataTable().ajax.reload();
            });

            $('#btn-limpiar').click(function() {
                $('#start_date').val('');
                $('#end_date').val('');
                $('#personnel_search').val('');

                $('#datatable').DataTable().ajax.reload();
            });
        });

        $('#btn-nuevo').click(function() {
            $.ajax({
                url: "{{ route('admin.attendances.create') }}",
                type: "GET",
                success: function(response) {
                    $('#FormModal .modal-title')
                        .html('<i class="fas fa-clipboard-check"></i> Nueva Asistencia');

                    $('#FormModal .modal-body').html(response);
                    $('#FormModal').modal("show");

                    $('.select2').select2({
                        theme: 'bootstrap4',
                        width: '100%',
                        dropdownParent: $('#FormModal')
                    });

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
                url: "{{ route('admin.attendances.edit', 'id') }}".replace('id', id),
                type: "GET",
                success: function(response) {
                    $('#FormModal .modal-title')
                        .html('<i class="fas fa-pen"></i> Modificar Asistencia');

                    $('#FormModal .modal-body').html(response);
                    $('#FormModal').modal("show");

                    $('.select2').select2({
                        theme: 'bootstrap4',
                        width: '100%',
                        dropdownParent: $('#FormModal')
                    });

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
        }
    </script>

@stop
