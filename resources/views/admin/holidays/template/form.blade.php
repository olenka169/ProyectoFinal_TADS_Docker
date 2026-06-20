<div class="form-group">
    <label>Fecha del Feriado <span class="text-danger">*</span></label>
    <input type="date" name="date" class="form-control"
        value="{{ isset($holiday) ? $holiday->date->format('Y-m-d') : '' }}" required>

    <small class="text-muted">
        Seleccione la fecha correspondiente al feriado
    </small>
</div>

<div class="form-group">
    <label>Descripción <span class="text-danger">*</span></label>
    <input type="text" name="description" class="form-control" value="{{ $holiday->description ?? '' }}"
        placeholder="Ingrese la descripción del feriado" required>
</div>

<div class="form-group">
    <label>Estado <span class="text-danger">*</span></label>
    <select name="status" class="form-control" required>
        <option value="1" {{ isset($holiday) && $holiday->status == 1 ? 'selected' : '' }}>
            Activo
        </option>

        <option value="0" {{ isset($holiday) && $holiday->status == 0 ? 'selected' : '' }}>
            Inactivo
        </option>
    </select>

    <small class="text-muted">
        Los feriados inactivos no se consideran en las validaciones de programación
    </small>
</div>

<div class="alert alert-info mb-3">
    <strong>
        <i class="fas fa-info-circle"></i> Información:
    </strong>

    <ul class="mb-0 mt-2">
        <li>Los días feriados afectan la programación de rutas.</li>
        <li>Los feriados inactivos no se consideran en las validaciones.</li>
        <li>Puede registrar feriados nacionales, regionales o municipales.</li>
    </ul>
</div>
