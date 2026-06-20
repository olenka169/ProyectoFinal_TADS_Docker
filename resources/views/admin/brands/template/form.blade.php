<div class="form-group">
    <label>Nombre de la Marca <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control" placeholder="Ingrese nombre de la marca"
        value="{{ $brand->name ?? '' }}" required>
</div>

<div class="form-group">
    <label>Descripción</label>
    <textarea name="description" class="form-control" rows="3" placeholder="Ingrese una descripción de la marca">{{ $brand->description ?? '' }}</textarea>
</div>

<div class="form-group">
    <label>Logo de la Marca</label>

    <input type="file" name="logo" class="form-control-file" accept="image/*">

    @isset($brand)
        <div class="mt-2" id="preview-container">
            @if ($brand->logo)
                <label class="text-muted d-block">Imagen actual:</label>
                <img src="{{ asset('storage/' . $brand->logo) }}" class="img-thumbnail" width="120"
                    alt="Logo de {{ $brand->name }}">
            @else
                <div class="alert alert-info py-1 px-2" style="font-size: 0.85rem;">
                    <i class="fas fa-info-circle"></i> No hay ninguna imagen registrada para esta marca.
                </div>
            @endif
        </div>
    @endisset
</div>
