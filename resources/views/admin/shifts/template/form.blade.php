<div class="form-group">
    <label for="name">Nombre del Turno <span class="text-danger">*</span></label>
    <input type="text" id="name" name="name" class="form-control" placeholder="Ingrese nombre del turno"
        value="{{ $shift->name ?? '' }}" required>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="start_time"> Hora de Inicio <span class="text-danger">*</span> </label>
            <input type="time" id="start_time" name="start_time" class="form-control"
                value="{{ $shift->start_time ?? '' }}" required>
            <small class="text-muted">
                Formato de 24 horas
            </small>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="end_time"> Hora de Término <span class="text-danger">*</span> </label>
            <input type="time" id="end_time" name="end_time" class="form-control"
                value="{{ $shift->end_time ?? '' }}" required>
            <small class="text-muted">
                Formato de 24 horas
            </small>
        </div>
    </div>
</div>

<div class="form-group">
    <label for="description">
        Descripción
    </label>
    <textarea id="description" name="description" class="form-control" rows="3"
        placeholder="Ingrese una descripción del turno">{{ $shift->description ?? '' }}</textarea>
</div>

<div class="alert alert-info">
    <strong>Nota:</strong>
    Configure los horarios de entrada y salida para este turno.
</div>
