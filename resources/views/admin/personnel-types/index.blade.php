@extends('adminlte::page')

@section('title', 'Tipos de Personal')

@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)

@section('content')
    <div class="pt-3"></div>
    <div class="card">
        <div class="card-header">
            <button type="button" class="btn btn-primary btn-sm float-right" id="btn-nuevo">
                <i class="fas fa-plus"></i>
                Nuevo Tipo
            </button>
            <h4>
                <i class="fas fa-user-tag"></i> Lista de Tipos de Personal
            </h4>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-striped table-hover table-sm text-nowrap" id="datatable">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Creación</th>
                        <th>Actualización</th>
                        <th width="120">Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="modal fade" id="FormModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"></h5>
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
                    [0, 'asc']
                ],
                ajax: "{{ route('admin.personnel-types.index') }}",
                columns: [{
                        data: "name"
                    },
                    {
                        data: "description"
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
                    url: 'https://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json',
                }
            });
        });

        $('#btn-nuevo').click(function() {

            $.ajax({

                url: "{{ route('admin.personnel-types.create') }}",
                type: "GET",

                success: function(response) {

                    $('#FormModal .modal-title').html(
                        '<i class="fas fa-user-tag"></i> Nuevo Tipo de Personal'
                    );

                    $('#FormModal .modal-body').html(response);

                    $('#FormModal').modal("show");

                    $('#FormModal form').on("submit", function(e) {

                        e.preventDefault();

                        enviarFormulario(this);
                    });
                }
            });
        });

        $(document).on('click', '.btn-editar', function() {

            let id = $(this).attr("id");

            $.ajax({

                url: "{{ route('admin.personnel-types.edit', 'id') }}"
                    .replace('id', id),

                type: "GET",

                success: function(response) {

                    $('#FormModal .modal-title').html(
                        '<i class="fas fa-pen"></i> Modificar Tipo de Personal'
                    );

                    $('#FormModal .modal-body').html(response);

                    $('#FormModal').modal("show");

                    $('#FormModal form').on("submit", function(e) {

                        e.preventDefault();

                        enviarFormulario(this);
                    });
                }
            });
        });

        function enviarFormulario(formulario) {
            let form = $(formulario);

            $.ajax({

                url: form.attr('action'),
                type: form.attr('method'),
                data: new FormData(formulario),

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

                    Swal.fire(
                        'Ocurrió un error',
                        xhr.responseJSON.message,
                        'error'
                    );
                }
            });
        }

        $(document).on('click', '.btn-delete', function(e) {

            e.preventDefault();

            let url = $(this).data('url');

            Swal.fire({

                title: '¿Está seguro?',
                text: 'Esta acción es irreversible',
                type: 'warning',

                showCancelButton: true,

                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'

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

                            Swal.fire(
                                'Error',
                                xhr.responseJSON.message,
                                'error'
                            );
                        }
                    });
                }
            });
        });

        function refreshTable() {
            $('#datatable').DataTable()
                .ajax
                .reload(null, false);
        }
    </script>
@stop
