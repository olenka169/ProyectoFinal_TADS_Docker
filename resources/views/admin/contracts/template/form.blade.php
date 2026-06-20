<div class="row">
    <div class="col-md-12 form-group position-relative">

        <label>Personal <span class="text-danger">*</span></label>

        @php
            $selectedPersonnelText = '';

            if (isset($contract) && $contract->personnel) {
                $selectedPersonnelText =
                    $contract->personnel->dni .
                    ' - ' .
                    $contract->personnel->names .
                    ' ' .
                    $contract->personnel->lastnames;
            }
        @endphp

        <input type="hidden" name="personnel_id" id="personnel_id" value="{{ $contract->personnel_id ?? '' }}">

        <div class="position-relative">

            <input type="text" id="personnel_search_input" class="form-control pr-5"
                placeholder="Busque por DNI, nombres o apellidos del personal" value="{{ $selectedPersonnelText }}"
                autocomplete="off" required>

            <button type="button" id="clear_personnel_search" class="btn btn-sm text-muted"
                style="position:absolute; right:8px; top:50%; transform:translateY(-50%); display:none;">
                <i class="fas fa-times"></i>
            </button>

        </div>

        <div id="personnel_results" class="list-group shadow-sm d-none"
            style="position:absolute; z-index:9999; left:0; right:0; max-height:180px; overflow-y:auto;">

            @foreach ($personnels as $person)
                <button type="button" class="list-group-item list-group-item-action personnel-option"
                    data-id="{{ $person->id }}"
                    data-text="{{ $person->dni }} - {{ $person->names }} {{ $person->lastnames }}">

                    <strong>{{ $person->dni }}</strong>
                    - {{ $person->names }} {{ $person->lastnames }}

                </button>
            @endforeach

        </div>

        <small class="text-muted">
            Seleccione al personal para continuar con el contrato
        </small>

    </div>

    <div class="col-md-6 form-group">
        <label for="type">Tipo de Contrato <span class="text-danger">*</span></label>
        <select name="type" id="type" class="form-control" required>
            <option value="Permanente" {{ isset($contract) && $contract->type == 'Permanente' ? 'selected' : '' }}>
                Permanente
            </option>
            <option value="Nombrado" {{ isset($contract) && $contract->type == 'Nombrado' ? 'selected' : '' }}>
                Nombrado
            </option>
            <option value="Temporal" {{ isset($contract) && $contract->type == 'Temporal' ? 'selected' : '' }}>
                Temporal
            </option>
        </select>
    </div>

    <div class="col-md-6 form-group">
        <label for="salary">Salario <span class="text-danger">*</span></label>
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text">S/</span>
            </div>
            <input type="number" name="salary" id="salary" class="form-control" step="0.01" min="0"
                value="{{ $contract->salary ?? '' }}" required placeholder="0.00">
        </div>
    </div>

    <div class="col-md-6 form-group">
        <label for="start_date">Fecha de Inicio <span class="text-danger">*</span></label>
        <input type="date" name="start_date" id="start_date" class="form-control"
            value="{{ isset($contract) ? $contract->start_date->format('Y-m-d') : '' }}" required>
    </div>

    <div class="col-md-6 form-group">
        <label for="end_date">Fecha de Fin</label>
        <input type="date" name="end_date" id="end_date" class="form-control"
            value="{{ isset($contract) && $contract->end_date ? $contract->end_date->format('Y-m-d') : '' }}">
    </div>

    <div class="col-md-12 form-group">
        <label for="probation_period">Periodo de Prueba (meses)</label>
        <input type="text" name="probation_period" id="probation_period" class="form-control"
            value="{{ $contract->probation_period ?? '' }}" placeholder="Ej: 3 meses">
        <small class="text-muted">
            Periodo de prueba para contrato PERMANENTE
        </small>
    </div>

    <div class="col-md-12 form-group">
        <div class="custom-control custom-switch">
            <input type="hidden" name="is_active" value="0">

            <input type="checkbox" class="custom-control-input"
                id="{{ isset($contract) ? 'is_active_edit' : 'is_active' }}" name="is_active" value="1"
                {{ !isset($contract) || $contract->is_active ? 'checked' : '' }}>

            <label class="custom-control-label" for="{{ isset($contract) ? 'is_active_edit' : 'is_active' }}">
                Contrato Activo
            </label>
        </div>

        <small class="text-muted">
            Si se marca como activo, se desactivarán otros contratos previos de esta persona.
        </small>
    </div>
</div>

<script>
    $('#personnel_search_input').on('keyup focus', function() {

        let value = $(this).val().toLowerCase();
        let hasResults = false;

        $('.personnel-option').each(function() {

            let text = $(this).data('text').toLowerCase();

            if (text.includes(value)) {
                $(this).removeClass('d-none');
                hasResults = true;
            } else {
                $(this).addClass('d-none');
            }

        });

        if (hasResults) {
            $('#personnel_results').removeClass('d-none');
        } else {
            $('#personnel_results').addClass('d-none');
        }

    });

    $(document).on('click', '.personnel-option', function() {

        $('#personnel_id').val($(this).data('id'));

        $('#personnel_search_input').val($(this).data('text'));

        $('#personnel_results').addClass('d-none');

        $('#clear_personnel_search').show();

    });

    $(document).on('click', function(e) {

        if (!$(e.target).closest('#personnel_search_input, #personnel_results').length) {
            $('#personnel_results').addClass('d-none');
        }

    });

    $('#clear_personnel_search').on('click', function() {

        $('#personnel_id').val('');

        $('#personnel_search_input').val('');

        $('#personnel_results').addClass('d-none');

        $('#clear_personnel_search').hide();

    });

    $('#personnel_search_input').on('input', function() {

        $('#personnel_id').val('');

        if ($(this).val().trim() !== '') {
            $('#clear_personnel_search').show();
        } else {
            $('#clear_personnel_search').hide();
        }

    });

    if ($('#personnel_search_input').val().trim() !== '') {
        $('#clear_personnel_search').show();
    }
</script>
