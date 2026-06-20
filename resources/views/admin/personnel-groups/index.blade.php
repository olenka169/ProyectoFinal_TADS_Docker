@extends('adminlte::page')

@section('title', 'Grupos de Personal')

@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)

@section('content')

    <div class="pt-3"></div>

    <div class="card">

        <div class="card-header">

            <button type="button" class="btn btn-primary btn-sm float-right" id="btn-nuevo">

                <i class="fas fa-plus"></i>
                Nuevo Grupo

            </button>

            <h4>

                <i class="fas fa-users"></i>
                Lista de Grupos de Personal

            </h4>

        </div>

        <div class="card-body table-responsive">
            <table class="table table-striped table-hover table-sm text-nowrap" id="datatable">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Zona</th>
                        <th>Turno</th>
                        <th>Vehículo</th>
                        <th>Conductor</th>
                        <th>Ayudantes</th>
                        <th>Días de Trabajo</th>
                        <th>Estado</th>
                        <th>Creación</th>
                        <th width="20">Editar</th>
                        <th width="20">Eliminar</th>

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
                        Formulario de Grupo
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
                ajax: "{{ route('admin.personnel-groups.index') }}",
                columns: [

                    {
                        data: 'name'
                    },
                    {
                        data: 'zone'
                    },
                    {
                        data: 'shift'
                    },
                    {
                        data: 'vehicle'
                    },
                    {
                        data: 'driver'
                    },
                    {
                        data: 'helpers',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'workdays'
                    },
                    {
                        data: 'status_badge',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'created_at_format'
                    },
                    {
                        data: 'edit',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'delete',
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

                url: "{{ route('admin.personnel-groups.create') }}",

                type: "GET",

                success: function(response) {

                    $('#FormModal .modal-title').html(
                        '<i class="fas fa-users"></i> Nuevo Grupo'
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

                url: "{{ route('admin.personnel-groups.edit', 'id') }}"
                    .replace('id', id),

                type: "GET",

                success: function(response) {

                    $('#FormModal .modal-title').html(
                        '<i class="fas fa-pen"></i> Modificar Grupo'
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

                    console.log(xhr);

                    Swal.fire(
                        'Error',
                        xhr.responseJSON?.message ??
                        xhr.responseText ??
                        'Error interno del servidor',
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

                icon: 'warning',

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
            $('#datatable')
                .DataTable()
                .ajax
                .reload(null, false);
        }
    </script>

@stop
