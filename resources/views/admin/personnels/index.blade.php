@extends('adminlte::page')

@section('title', 'Personal')

@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)

@section('content')

    <div class="pt-3"></div>

    <div class="card">
        <div class="card-header">
            <button type="button" class="btn btn-primary btn-sm float-right" id="btn-nuevo">
                <i class="fas fa-plus"></i> Nuevo Personal
            </button>
            <h4>
                <i class="fas fa-user"></i>
                Lista de Personal
            </h4>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-striped table-hover table-sm text-nowrap" id="datatable">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>DNI</th>
                        <th>Nombres</th>
                        <th>Apellidos</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Creación</th>
                        <th>Actualización</th>
                        <th width="140">Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="modal fade" id="FormModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        Formulario de Personal
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
                    [2, 'asc']
                ],
                ajax: "{{ route('admin.personnels.index') }}",
                columns: [{
                        data: "photo",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: "dni"
                    },
                    {
                        data: "names"
                    },
                    {
                        data: "lastnames"
                    },
                    {
                        data: "type_name"
                    },
                    {
                        data: "status_badge"
                    },
                    {
                        data: "created_at"
                    },
                    {
                        data: "updated_at"
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
                url: "{{ route('admin.personnels.create') }}",
                type: "GET",
                success: function(response) {
                    $('#FormModal .modal-title')
                        .html('<i class="fas fa-user"></i> Nuevo Personal');
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

        $(document).on('click', '.btn-ver', function() {
            let id = $(this).attr("id");
            $.ajax({
                url: "{{ route('admin.personnels.show', 'id') }}"
                    .replace('id', id),
                type: "GET",
                success: function(response) {
                    $('#FormModal .modal-title')
                        .html('<i class="fas fa-user"></i> Información del Personal');
                    $('#FormModal .modal-body')
                        .html(response);
                    $('#FormModal').modal("show");
                }
            });
        });

        $(document).on('click', '.btn-editar', function() {
            let id = $(this).attr("id");
            $.ajax({
                url: "{{ route('admin.personnels.edit', 'id') }}"
                    .replace('id', id),
                type: "GET",
                success: function(response) {
                    $('#FormModal .modal-title')
                        .html('<i class="fas fa-pen"></i> Modificar Personal');
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
                        response.message,
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
    </script>

@stop
