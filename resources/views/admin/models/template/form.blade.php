<div class="form-group">
    <label for="name">Nombre del Modelo <span class="text-danger">*</span></label>
    <input type="text" name="name" id="name" class="form-control" placeholder="Ej: Corolla, Hilux..."
        value="{{ isset($model) ? $model->name : '' }}" required>
</div>

<div class="form-group">
    <label for="code">Código del Modelo <span class="text-danger">*</span></label>
    <input type="text" name="code" id="code" class="form-control" placeholder="Ej: TOY-COR-2024"
        value="{{ isset($model) ? $model->code : '' }}" required>
</div>

<div class="form-group">
    <label for="brand_id">Marca <span class="text-danger">*</span></label>
    <select name="brand_id" id="brand_id" class="form-control" required>
        <option value="">Seleccione una marca</option>
        @foreach ($brands as $brand)
            <option value="{{ $brand->id }}" {{ isset($model) && $model->brand_id == $brand->id ? 'selected' : '' }}>
                {{ $brand->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label for="description">Descripción</label>
    <textarea name="description" id="description" class="form-control" rows="3"
        placeholder="Ingrese una descripción del modelo">{{ isset($model) ? $model->description : '' }}</textarea>
</div>
