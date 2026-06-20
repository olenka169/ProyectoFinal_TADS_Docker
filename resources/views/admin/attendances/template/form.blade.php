<div class="form-group position-relative">
    <label>Personal <span class="text-danger">*</span></label>

    @php
        $selectedPersonnelText = '';

        if (isset($attendance) && $attendance->personnel) {
            $selectedPersonnelText =
                $attendance->personnel->dni .
                ' - ' .
                $attendance->personnel->names .
                ' ' .
                $attendance->personnel->lastnames;
        }
    @endphp

    <input type="hidden" name="personnel_id" id="personnel_id" value="{{ $attendance->personnel_id ?? '' }}">

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
                <strong>{{ $person->dni }}</strong> - {{ $person->names }} {{ $person->lastnames }}
            </button>
        @endforeach
    </div>

    <small class="text-muted">
        Seleccione al personal para consultar sus registros del día
    </small>
</div>

<div id="personnel-info-box" class="card border-secondary mb-3">
    <div class="card-header bg-light">
        <strong>
            <i class="fas fa-history text-info"></i>
            Registros de asistencia del día
        </strong>
    </div>

    <div class="card-body" id="personnel-empty-state">
        <div class="text-center text-muted py-3">
            <i class="fas fa-search fa-2x mb-2"></i>
            <p class="mb-0">
                Seleccione un personal para visualizar sus registros de asistencia del día.
            </p>
        </div>
    </div>

    <div class="card-body d-none" id="personnel-loaded-state">

        <div id="records-list" class="mb-2 text-muted">
            No hay registros para esta fecha.
        </div>

        <div id="attendance-message" class="alert py-2 mb-0"></div>
    </div>
</div>

<div class="row">

    <div class="col-md-4">
        <div class="form-group">
            <label>Fecha <span class="text-danger">*</span></label>
            <input type="date" name="date" class="form-control"
                value="{{ isset($attendance) ? $attendance->date->format('Y-m-d') : now()->format('Y-m-d') }}" required>
            <small class="text-muted">
                Seleccione la fecha de asistencia
            </small>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Hora <span class="text-danger">*</span></label>
            <input type="time" name="time" class="form-control"
                value="{{ isset($attendance) ? \Carbon\Carbon::parse($attendance->time)->format('H:i') : now()->format('H:i') }}"
                required>
            <small class="text-muted">
                Seleccione la hora de registro
            </small>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Turno <span class="text-danger">*</span></label>
            <input type="text" id="shift_preview" class="form-control" value="Se asignará automáticamente" disabled>
            <small class="text-muted">
                El sistema asigna el turno según la hora
            </small>
        </div>
    </div>

</div>

<div class="row">

    <div class="col-md-6">
        <div class="form-group">
            <label>Tipo de Marcación <span class="text-danger">*</span></label>

            <input type="text" id="type_preview" class="form-control"
                value="{{ isset($attendance) ? $attendance->type : 'Ingreso' }}" disabled>

            <small class="text-muted">
                El sistema asigna el tipo según los registros del día
            </small>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>Estado <span class="text-danger">*</span></label>
            <select name="status" class="form-control" required>
                <option value="Presente"
                    {{ isset($attendance) && $attendance->status == 'Presente' ? 'selected' : '' }}>
                    Presente
                </option>

                <option value="Ausente" {{ isset($attendance) && $attendance->status == 'Ausente' ? 'selected' : '' }}>
                    Ausente
                </option>
            </select>
            <small class="text-muted">
                Seleccione el estado de asistencia
            </small>
        </div>
    </div>

</div>

<div class="form-group">
    <label>Notas adicionales</label>
    <textarea name="notes" class="form-control" rows="3"
        placeholder="Ingrese notas adicionales sobre la asistencia">{{ $attendance->notes ?? '' }}</textarea>
</div>

<script>
    window.attendanceShifts = @json($shifts);

    function updateShiftPreview() {
        let time = $('input[name="time"]').val();

        if (!time) {
            $('#shift_preview').val('Se asignará automáticamente');
            return;
        }

        let selectedShift = null;

        window.attendanceShifts.forEach(function(shift) {
            let start = shift.start_time.substring(0, 5);
            let end = shift.end_time.substring(0, 5);

            if (start <= end) {
                if (time >= start && time < end) {
                    selectedShift = shift;
                }
            } else {
                if (time >= start || time < end) {
                    selectedShift = shift;
                }
            }
        });

        if (selectedShift) {
            $('#shift_preview').val(
                selectedShift.name + ' (' +
                selectedShift.start_time.substring(0, 5) + ' - ' +
                selectedShift.end_time.substring(0, 5) + ')'
            );
        } else {
            $('#shift_preview').val('Sin turno asignado');
        }
    }

    $('#personnel_search_input').off('keyup focus').on('keyup focus', function() {
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

    $(document).off('click', '.personnel-option').on('click', '.personnel-option', function() {
        let id = $(this).data('id');
        let text = $(this).data('text');

        $('#personnel_id').val(id);
        $('#personnel_search_input').val(text);
        $('#personnel_results').addClass('d-none');
        $('#clear_personnel_search').show();

        loadPersonnelDayInfo();
    });

    $(document).off('click.personnelResults').on('click.personnelResults', function(e) {
        if (!$(e.target).closest('#personnel_search_input, #personnel_results').length) {
            $('#personnel_results').addClass('d-none');
        }
    });

    function loadPersonnelDayInfo() {
        let personnelId = $('#personnel_id').val();
        let date = $('input[name="date"]').val();

        if (!personnelId || !date) {
            $('#personnel-empty-state').removeClass('d-none');
            $('#personnel-loaded-state').addClass('d-none');

            $('#type_preview').val('Ingreso');
            $('#FormModal button[type="submit"]').prop('disabled', false);
            return;
        }

        $.ajax({
            url: "{{ route('admin.attendances.personnel-day-info') }}",
            type: "GET",
            data: {
                personnel_id: personnelId,
                date: date
            },
            success: function(response) {
                $('#personnel-empty-state').addClass('d-none');
                $('#personnel-loaded-state').removeClass('d-none');

                let recordsHtml = '';

                if (response.records.length === 0) {
                    recordsHtml = '<span class="text-muted">No hay registros para esta fecha.</span>';
                } else {
                    response.records.forEach(function(record) {
                        let badge = record.type === 'Ingreso' ? 'success' : 'info';

                        recordsHtml += `
                            <div class="attendance-record-item">
                                <div>
                                    <strong>${record.type}</strong>
                                    <small class="text-muted d-block">${record.time} - ${record.status}</small>
                                </div>
                            </div>
                        `;
                    });
                }

                $('#records-list').html(recordsHtml);

                if (response.can_register) {
                    $('#type_preview').val(response.next_type);

                    if (response.next_type === 'Ingreso') {
                        $('#attendance-message')
                            .removeClass('alert-success alert-warning alert-danger')
                            .addClass('alert-info')
                            .html('<i class="fas fa-info-circle"></i> ' + response.message);
                    } else {
                        $('#attendance-message')
                            .removeClass('alert-success alert-warning alert-danger')
                            .addClass('alert-warning')
                            .html('<i class="fas fa-info-circle"></i> ' + response.message);
                    }

                    $('#FormModal button[type="submit"]').prop('disabled', false);
                }
            }
        });
    }

    $('input[name="date"]').off('change').on('change', loadPersonnelDayInfo);

    $('input[name="time"]').off('change').on('change', function() {
        updateShiftPreview();
    });

    loadPersonnelDayInfo();
    updateShiftPreview();

    function resetPersonnelSearch() {
        $('#personnel_id').val('');
        $('#personnel_search_input').val('');
        $('#personnel_results').addClass('d-none');
        $('#clear_personnel_search').hide();

        $('#personnel-empty-state').removeClass('d-none');
        $('#personnel-loaded-state').addClass('d-none');

        $('#records-list').html('No hay registros para esta fecha.');
        $('#attendance-message').removeClass('alert-info alert-warning alert-danger').html('');

        $('#type_preview').val('Ingreso');
        $('#FormModal button[type="submit"]').prop('disabled', false);
    }

    $('#clear_personnel_search').off('click').on('click', function() {
        resetPersonnelSearch();
    });

    $('#personnel_search_input').off('input.clearButton').on('input.clearButton', function() {
        $('#personnel_id').val('');

        if ($(this).val().trim() !== '') {
            $('#clear_personnel_search').show();
        } else {
            resetPersonnelSearch();
        }
    });

    if ($('#personnel_search_input').val().trim() !== '') {
        $('#clear_personnel_search').show();
    }
</script>
