<div class="form-group">
    <label for="name">Nombre del Tipo de Vehículo <span class="text-danger">*</span></label>
    <input type="text" name="name" id="name" class="form-control"
        placeholder="Ingrese nombre del tipo de vehículo" value="{{ isset($type) ? $type->name : '' }}" required>
</div>

<div class="form-group">
    <label for="description">Descripción</label>
    <textarea name="description" id="description" class="form-control" rows="3"
        placeholder="Ingrese una descripción del tipo de vehículo">{{ isset($type) ? $type->description : '' }}</textarea>
</div>
