@extends('adminlte::page')

@section('title', 'Modelos de Vehículos')

@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)

@section('content')
    <div class="pt-3"></div>

    <div class="card">
        <div class="card-header">
            <button type="button" class="btn btn-primary btn-sm float-right" id="btn-nuevo">
                <i class="fas fa-plus"></i> Nuevo Modelo
            </button>
            <h4><i class="fas fa-wrench"></i> Lista de Modelos de Vehículos</h4>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-striped table-hover table-sm text-nowrap" id="datatable">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Código</th>
                        <th>Marca</th>
                        <th>Descripción</th>
                        <th>Creación</th>
                        <th>Actualización</th>
                        <th width="120">Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="modal fade" id="FormModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Formulario de Modelo</h5>
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
                ajax: "{{ route('admin.models.index') }}",
                columns: [{
                        data: "name"
                    },
                    {
                        data: "code"
                    },
                    {
                        data: "brand_name"
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
                    },
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json',
                },
            });
        });

        $('#btn-nuevo').click(function() {
            $.ajax({
                url: "{{ route('admin.models.create') }}",
                type: "GET",
                success: function(response) {
                    $('#FormModal .modal-title').html('<i class="fas fa-wrench"></i> Nuevo Modelo');
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
                url: "{{ route('admin.models.edit', 'id') }}".replace('id', id),
                type: "GET",
                success: function(response) {
                    $('#FormModal .modal-title').html('<i class="fas fa-pen"></i> Modificar Modelo');
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
                    Swal.fire('Proceso exitoso', response.message, 'success');
                },
                error: function(xhr) {
                    let response = xhr.responseJSON;
                    Swal.fire('Ocurrió un error', response.message, 'error');
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
                            Swal.fire('Proceso exitoso', response.message, 'success');
                        },
                        error: function(xhr) {
                            let response = xhr.responseJSON;
                            Swal.fire('Ocurrió un error', response ? response.message :
                                'No se pudo eliminar', 'error');
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
