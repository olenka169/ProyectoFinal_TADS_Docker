@extends('adminlte::page')

@section('title', 'Contratos')

@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)
@section('plugins.Select2', true)

@section('content')

    <div class="pt-3"></div>

    <div class="card">

        <div class="card-header">

            <button type="button" class="btn btn-primary btn-sm float-right" id="btn-nuevo">

                <i class="fas fa-plus"></i>
                Nuevo Contrato

            </button>

            <h4>
                <i class="fas fa-file-contract"></i>
                Lista de Contratos
            </h4>

        </div>

        <div class="card-body table-responsive">
            <table class="table table-striped table-hover table-sm text-nowrap" id="datatable">

                <thead>
                    <tr>
                        <th>DNI</th>
                        <th>Personal</th>
                        <th>Tipo</th>
                        <th>F. Inicio</th>
                        <th>F. Fin</th>
                        <th>Salario</th>
                        <th>Estado</th>
                        <th width="120">Acciones</th>
                    </tr>
                </thead>

            </table>

        </div>

    </div>

    <div class="modal fade" id="FormModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
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
                    [3, 'desc']
                ],
                ajax: "{{ route('admin.contracts.index') }}",
                columns: [{
                        data: "personnel_dni",
                        name: "personnels.dni"
                    },
                    {
                        data: "personnel_name",
                        name: "personnels.names"
                    },
                    {
                        data: "type",
                        name: "contracts.type"
                    },
                    {
                        data: "start_date",
                        name: "contracts.start_date"
                    },
                    {
                        data: "end_date",
                        name: "contracts.end_date"
                    },
                    {
                        data: "salary",
                        name: "contracts.salary"
                    },
                    {
                        data: "status",
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
                    url: 'https://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json',
                }
            });
        });

        $('#btn-nuevo').click(function() {

            $.ajax({

                url: "{{ route('admin.contracts.create') }}",
                type: "GET",

                success: function(response) {

                    $('#FormModal .modal-title').html(
                        '<i class="fas fa-file-contract"></i> Nuevo Contrato'
                    );

                    $('#FormModal .modal-body').html(response);

                    $('#FormModal').modal("show");

                    $('.select2').select2({
                        theme: 'bootstrap4',
                        width: '100%',
                        dropdownParent: $('#FormModal')
                    });

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

                url: "{{ route('admin.contracts.edit', 'id') }}"
                    .replace('id', id),

                type: "GET",

                success: function(response) {

                    $('#FormModal .modal-title').html(
                        '<i class="fas fa-pen"></i> Modificar Contrato'
                    );

                    $('#FormModal .modal-body').html(response);

                    $('#FormModal').modal("show");

                    $('.select2').select2({
                        theme: 'bootstrap4',
                        width: '100%',
                        dropdownParent: $('#FormModal')
                    });

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
                    let message = 'Ocurrió un error inesperado';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }

                    Swal.fire(
                        'Error',
                        message,
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
                text: 'Esta acción desactivará o eliminará el contrato permanentemente',
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
            $('#datatable').DataTable()
                .ajax
                .reload(null, false);
        }
    </script>
@stop
