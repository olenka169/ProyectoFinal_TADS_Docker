<div class="form-group">
    <label for="name">Nombre del Color <span class="text-danger">*</span></label>
    <input type="text" name="name" id="name" class="form-control" placeholder="Ingrese nombre del color"
        value="{{ isset($color) ? $color->name : '' }}" required>
</div>

<div class="form-group">
    <label for="code">Código del Color <span class="text-danger">*</span></label>

    <div class="input-group">
        <input type="text" name="code" id="code" class="form-control" placeholder="#0088CE"
            value="{{ isset($color) ? $color->code : '#0088CE' }}" required>

        <div class="input-group-append">
            <input type="color" id="color_picker" class="form-control" style="width: 60px;"
                value="{{ isset($color) ? $color->code : '#0088CE' }}">
        </div>
    </div>

    <small class="text-muted">Seleccione un color o ingrese el código hexadecimal.</small>
</div>

<div class="form-group">
    <label for="description">Descripción</label>
    <textarea name="description" id="description" class="form-control" rows="3"
        placeholder="Ingrese una descripción del color">{{ isset($color) ? $color->description : '' }}</textarea>
</div>

<div class="form-group">
    <label>Vista Previa del Color</label>
    <div id="color_preview_box" class="text-white text-center font-weight-bold p-3 rounded"
        style="background: {{ isset($color) ? $color->code : '#0088CE' }};">
        {{ isset($color) ? $color->code : '#0088CE' }}
    </div>
</div>

<script>
    $('#color_picker').on('input', function() {
        $('#code').val($(this).val().toUpperCase());
        $('#color_preview_box')
            .css('background', $(this).val())
            .text($(this).val().toUpperCase());
    });

    $('#code').on('input', function() {
        let color = $(this).val();

        if (/^#[0-9A-Fa-f]{6}$/.test(color)) {
            $('#color_picker').val(color);
            $('#color_preview_box')
                .css('background', color)
                .text(color.toUpperCase());
        }
    });
</script>
