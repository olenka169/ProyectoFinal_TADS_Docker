<div class="form-group">
    <label for="name">Nombre del Tipo de Personal <span class="text-danger">*</span></label>

    <input type="text" id="name" name="name" class="form-control"
        placeholder="Ingrese nombre del tipo de personal" value="{{ $type->name ?? '' }}" required>
</div>

<div class="form-group">
    <label for="description">Descripción</label>

    <textarea id="description" name="description" class="form-control" rows="3"
        placeholder="Ingrese una descripción del tipo de personal">{{ $type->description ?? '' }}</textarea>
</div>
