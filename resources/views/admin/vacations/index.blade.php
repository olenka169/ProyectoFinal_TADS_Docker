@extends('adminlte::page')

@section('title', 'Vacaciones')

@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)

@section('content')

    <div class="pt-3"></div>

    <div class="card">
        <div class="card-header">
            <button type="button" class="btn btn-primary btn-sm float-right" id="btn-nuevo">
                <i class="fas fa-plus"></i> Nueva Solicitud
            </button>

            <h4>
                <i class="fas fa-plane-departure"></i>
                Lista de Vacaciones
            </h4>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-striped table-hover table-sm text-nowrap" id="datatable">
                <thead>
                    <tr>
                        <th>DNI</th>
                        <th>Personal</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Días</th>
                        <th>Estado</th>
                        <th width="180">Acciones</th>
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
                        Formulario de Vacaciones
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
        $(document).ready(function() {
            $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                autoWidth: false,
                order: [
                    [2, 'desc']
                ],
                ajax: "{{ route('admin.vacations.index') }}",
                columns: [{
                        data: "personnel_dni"
                    },
                    {
                        data: "personnel_name"
                    },
                    {
                        data: "start_date"
                    },
                    {
                        data: "end_date"
                    },
                    {
                        data: "requested_days"
                    },
                    {
                        data: "status_badge",
                        orderable: false,
                        searchable: false
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
        });

        $('#btn-nuevo').click(function() {
            $.ajax({
                url: "{{ route('admin.vacations.create') }}",
                type: "GET",
                success: function(response) {
                    $('#FormModal .modal-title')
                        .html('<i class="fas fa-plane-departure"></i> Nueva Solicitud de Vacaciones');

                    $('#FormModal .modal-body')
                        .html(response);

                    $('#FormModal').modal("show");

                    initFormSubmit();
                }
            });
        });

        $(document).off('click.vacationShow', '.btn-ver')
            .on('click.vacationShow', '.btn-ver', function(e) {
                e.preventDefault();
                e.stopPropagation();

                let id = $(this).data("id");

                $.ajax({
                    url: "{{ route('admin.vacations.show', 'id') }}".replace('id', id),
                    type: "GET",
                    beforeSend: function() {
                        $('#FormModal .modal-title')
                            .html('<i class="fas fa-spinner fa-spin"></i> Cargando información');

                        $('#FormModal .modal-body')
                            .html(`
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-spinner fa-spin fa-2x mb-2"></i>
                            <p class="mb-0">Cargando información de vacaciones...</p>
                        </div>
                    `);

                        $('#FormModal').modal("show");
                    },
                    success: function(response) {
                        $('#FormModal .modal-title')
                            .html('<i class="fas fa-eye"></i> Información de Vacaciones');

                        $('#FormModal .modal-body')
                            .html(response);
                    },
                    error: function(xhr) {
                        let response = xhr.responseJSON;

                        $('#FormModal').modal("hide");

                        Swal.fire(
                            'Ocurrió un error',
                            response ? response.message :
                            'No se pudo cargar la información de vacaciones.',
                            'error'
                        );
                    }
                });
            });

        $(document).on('click', '.btn-editar', function() {
            let id = $(this).data("id");

            $.ajax({
                url: "{{ route('admin.vacations.edit', 'id') }}"
                    .replace('id', id),
                type: "GET",
                success: function(response) {
                    $('#FormModal .modal-title')
                        .html('<i class="fas fa-pen"></i> Modificar Solicitud de Vacaciones');

                    $('#FormModal .modal-body')
                        .html(response);

                    $('#FormModal').modal("show");

                    initFormSubmit();
                }
            });
        });

        $(document).on('click', '.btn-approve', function() {
            let id = $(this).data("id");

            confirmAction(
                "{{ route('admin.vacations.approve', 'id') }}".replace('id', id),
                "aprobar"
            );
        });

        $(document).on('click', '.btn-reject', function() {
            let id = $(this).data("id");

            confirmAction(
                "{{ route('admin.vacations.reject', 'id') }}".replace('id', id),
                "rechazar"
            );
        });

        function confirmAction(url, action) {
            Swal.fire({
                title: "¿Está seguro de " + action + " esta solicitud?",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Sí, " + action,
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed || result.value) {
                    $.post(url, {
                        _token: "{{ csrf_token() }}"
                    }, function(response) {
                        refreshTable();

                        Swal.fire(
                            'Proceso exitoso',
                            response.message,
                            'success'
                        );
                    }).fail(function(xhr) {
                        let response = xhr.responseJSON;

                        Swal.fire(
                            'Ocurrió un error',
                            response ? response.message : 'Error desconocido',
                            'error'
                        );
                    });
                }
            });
        }

        function initFormSubmit() {
            $('#FormModal form').off("submit").on("submit", function(e) {
                e.preventDefault();

                if (!this.checkValidity()) {
                    this.reportValidity();
                    return;
                }

                enviarFormulario(this);
            });
        }

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
                        response ? response.message : 'No se pudo procesar la solicitud',
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
            $('#datatable')
                .DataTable()
                .ajax.reload(null, false);
        }

        $('#FormModal').on('hidden.bs.modal', function() {
            $('#FormModal .modal-body').html('');
        });
    </script>

@stop
