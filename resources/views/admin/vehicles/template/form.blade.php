<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label for="code">Código <span class="text-danger">*</span></label>
            <input type="text" name="code" id="code" class="form-control" placeholder="VEH-001"
                value="{{ isset($vehicle) ? $vehicle->code : '' }}" required>
        </div>
    </div>
    <div class="col-md-5">
        <div class="form-group">
            <label for="name">Nombre del Vehículo <span class="text-danger">*</span></label>
            <input type="text" name="name" id="name" class="form-control" placeholder="Camión Recolector #1"
                value="{{ isset($vehicle) ? $vehicle->name : '' }}" required>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="plate">Placa <span class="text-danger">*</span></label>
            <input type="text" name="plate" id="plate" class="form-control" placeholder="ABC-123"
                value="{{ isset($vehicle) ? $vehicle->plate : '' }}" required>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="brand_model_id">Marca y Modelo <span class="text-danger">*</span></label>
            <select name="brand_model_id" id="brand_model_id" class="form-control" required>
                <option value="">Seleccione...</option>
                @foreach ($models as $model)
                    <option value="{{ $model->id }}"
                        {{ isset($vehicle) && $vehicle->brand_model_id == $model->id ? 'selected' : '' }}>
                        {{ $model->brand->name }} - {{ $model->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="vehicle_type_id">Tipo de Vehículo <span class="text-danger">*</span></label>
            <select name="vehicle_type_id" id="vehicle_type_id" class="form-control" required>
                <option value="">Seleccione...</option>
                @foreach ($types as $type)
                    <option value="{{ $type->id }}"
                        {{ isset($vehicle) && $vehicle->vehicle_type_id == $type->id ? 'selected' : '' }}>
                        {{ $type->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="vehicle_color_id">Color <span class="text-danger">*</span></label>
            <select name="vehicle_color_id" id="vehicle_color_id" class="form-control" required>
                <option value="">Seleccione...</option>
                @foreach ($colors as $color)
                    <option value="{{ $color->id }}"
                        {{ isset($vehicle) && $vehicle->vehicle_color_id == $color->id ? 'selected' : '' }}>
                        {{ $color->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label for="year">Año <span class="text-danger">*</span></label>
            <input type="number" name="year" id="year" class="form-control" placeholder="2024"
                value="{{ isset($vehicle) ? $vehicle->year : date('Y') }}" required>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label for="load_capacity">Cap. Carga (Tn) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" name="load_capacity" id="load_capacity" class="form-control"
                placeholder="0.00" value="{{ isset($vehicle) ? $vehicle->load_capacity : '' }}" required>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label for="fuel_capacity">Cap. Combustible (L) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" name="fuel_capacity" id="fuel_capacity" class="form-control"
                placeholder="0.00" value="{{ isset($vehicle) ? $vehicle->fuel_capacity : '' }}" required>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label for="passenger_capacity">Cap. Pasajeros <span class="text-danger">*</span></label>
            <input type="number" name="passenger_capacity" id="passenger_capacity" class="form-control"
                placeholder="0" value="{{ isset($vehicle) ? $vehicle->passenger_capacity : '' }}" required>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label for="compaction_capacity">Cap. Compactación (Tn) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" name="compaction_capacity" id="compaction_capacity"
                class="form-control" placeholder="0.00"
                value="{{ isset($vehicle) ? $vehicle->compaction_capacity : '' }}" required>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label for="mileage">Kilometraje (Km) <span class="text-danger">*</span></label>
            <input type="number" name="mileage" id="mileage" class="form-control" placeholder="0"
                value="{{ isset($vehicle) ? $vehicle->mileage : '0' }}" required>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="status">Estado <span class="text-danger">*</span></label>
            <select name="status" id="status" class="form-control" required>
                <option value="Activo" {{ isset($vehicle) && $vehicle->status == 'Activo' ? 'selected' : '' }}>
                    Activo</option>
                <option value="Mantenimiento"
                    {{ isset($vehicle) && $vehicle->status == 'Mantenimiento' ? 'selected' : '' }}>Mantenimiento
                </option>
                <option value="Inactivo" {{ isset($vehicle) && $vehicle->status == 'Inactivo' ? 'selected' : '' }}>
                    Inactivo</option>
            </select>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="engine_number">Número de Motor</label>
            <input type="text" name="engine_number" id="engine_number" class="form-control"
                placeholder="Ingrese número de motor" value="{{ isset($vehicle) ? $vehicle->engine_number : '' }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="chassis_number">Número de Chasis</label>
            <input type="text" name="chassis_number" id="chassis_number" class="form-control"
                placeholder="Ingrese número de chasis" value="{{ isset($vehicle) ? $vehicle->chassis_number : '' }}">
        </div>
    </div>
</div>

<div class="form-group">
    <label for="description">Descripción</label>
    <textarea name="description" id="description" class="form-control" rows="2"
        placeholder="Ingrese una descripción del vehículo">{{ isset($vehicle) ? $vehicle->description : '' }}</textarea>
</div>

<div class="form-group">
    <label for="images">Imágenes del Vehículo</label>
    <div class="custom-file">
        <input type="file" name="images[]" id="images" class="custom-file-input" multiple accept="image/*">
        <label class="custom-file-label" for="images">Seleccionar imágenes...</label>
    </div>
</div>

@if (isset($vehicle) && $vehicle->images->count() > 0)
    <div class="form-group">
        <label>Imágenes Actuales</label>
        <input type="hidden" name="profile_image_id" id="profile_image_id"
            value="{{ $vehicle->images->where('is_profile', true)->first()->id ?? ($vehicle->images->first()->id ?? '') }}">
        <div class="row">
            @foreach ($vehicle->images as $image)
                <div class="col-md-3 mb-2">
                    <div class="card shadow-sm h-100 img-card {{ $image->is_profile ? 'border-primary' : '' }}"
                        id="img-card-{{ $image->id }}">
                        <img src="{{ asset('storage/' . $image->path) }}" class="card-img-top"
                            style="height: 100px; object-fit: cover;">
                        <div class="card-body p-2 text-center">
                            <div class="d-flex justify-content-center" style="gap: 10px;">
                                <button type="button"
                                    class="btn btn-sm {{ $image->is_profile ? 'btn-primary' : 'btn-outline-primary' }} btn-set-profile px-3"
                                    title="Marcar como perfil" data-id="{{ $image->id }}">
                                    <i class="fas fa-user"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger btn-delete-image px-3"
                                    data-url="{{ route('admin.vehicles.delete-image', $image->id) }}"
                                    title="Eliminar imagen">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

<script>
    // Mostrar nombre del archivo en el input custom-file
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        if (this.files.length > 1) {
            fileName = this.files.length + ' archivos seleccionados';
        }
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });

    $('.btn-set-profile').click(function() {
        let btn = $(this);
        let id = btn.data('id');

        // Actualizar input oculto
        $('#profile_image_id').val(id);

        // Actualizar UI: remover clases de todos
        $('.img-card').removeClass('border-primary');
        $('.btn-set-profile').removeClass('btn-primary').addClass('btn-outline-primary');

        // Aplicar a la seleccionada
        $(`#img-card-${id}`).addClass('border-primary');
        btn.removeClass('btn-outline-primary').addClass('btn-primary');
    });

    $('.btn-delete-image').click(function() {
        let btn = $(this);
        let url = btn.data('url');

        Swal.fire({
            title: "¿Eliminar imagen?",
            text: "Esta acción no se puede deshacer",
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
                        btn.closest('.col-md-3').remove();
                        Swal.fire('Eliminado', response.message, 'success');

                        // Si se eliminó la imagen de perfil, recargar el modal para ver el nuevo perfil asignado
                        if (btn.closest('.card').hasClass('border-primary')) {
                            let id = "{{ $vehicle->id ?? '' }}";
                            if (id) {
                                $.ajax({
                                    url: "{{ route('admin.vehicles.edit', 'id') }}"
                                        .replace('id', id),
                                    type: "GET",
                                    success: function(response) {
                                        $('#FormModal .modal-body').html(
                                            response);
                                    }
                                });
                            }
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'No se pudo eliminar la imagen', 'error');
                    }
                });
            }
        });
    });
</script>
