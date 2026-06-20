<div class="row">
    <div class="col-md-12">
        <div class="form-group position-relative">
            <label>Personal <span class="text-danger">*</span></label>

            @php
                $selectedPersonnelText = '';

                if (isset($vacation) && $vacation->personnel) {
                    $selectedPersonnelText =
                        $vacation->personnel->dni .
                        ' - ' .
                        $vacation->personnel->names .
                        ' ' .
                        $vacation->personnel->lastnames;
                }
            @endphp

            <input type="hidden" name="personnel_id" id="personnel_id" value="{{ $vacation->personnel_id ?? '' }}">

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
                @foreach ($personnels as $p)
                    <button type="button" class="list-group-item list-group-item-action personnel-option"
                        data-id="{{ $p->id }}"
                        data-text="{{ $p->dni }} - {{ $p->names }} {{ $p->lastnames }}">
                        <strong>{{ $p->dni }}</strong> - {{ $p->names }} {{ $p->lastnames }}
                    </button>
                @endforeach
            </div>

            <small id="days-info" class="form-text text-muted"></small>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>Fecha de inicio <span class="text-danger">*</span></label>
            <input type="date" name="start_date" id="start_date_form" class="form-control" required
                value="{{ isset($vacation) ? $vacation->start_date->format('Y-m-d') : '' }}"
                @if (!isset($vacation)) min="{{ date('Y-m-d') }}" @endif>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>Días solicitados <span class="text-danger">*</span></label>
            <input type="number" name="requested_days" id="requested_days" class="form-control" required min="1"
                max="30" placeholder="Número de días" value="{{ $vacation->requested_days ?? '' }}">
        </div>
    </div>

    <div class="col-md-12">
        <div class="form-group">
            <label>Fecha de fin (Calculada) <span class="text-danger">*</span></label>
            <input type="text" id="end_date_display" class="form-control" readonly
                placeholder="Se calculará automáticamente"
                value="{{ isset($vacation) ? $vacation->end_date->format('d/m/Y') : '' }}">
        </div>
    </div>

    <div class="col-md-12">
        <div class="form-group">
            <label>Notas</label>
            <textarea name="notes" class="form-control" rows="2"
                placeholder="Ingrese observaciones o comentarios sobre la solicitud">{{ $vacation->notes ?? '' }}</textarea>
        </div>
    </div>
</div>

<div class="alert alert-warning mb-3">
    <strong>
        <i class="fas fa-exclamation-triangle"></i> Importante:
    </strong>

    <ul class="mb-0 mt-2">
        <li>
            Solo personal <b>nombrado</b> y <b>contrato permanente</b> puede solicitar vacaciones.
        </li>
        <li>
            No se pueden solicitar vacaciones en fechas que coincidan con otras solicitudes aprobadas o pendientes.
        </li>
        <li>
            Las solicitudes pendientes pueden ser editadas o eliminadas.
        </li>
    </ul>
</div>

<script>
    $(document).ready(function() {

        function updateDaysInfo() {
            let id = $('#personnel_id').val();

            if (id) {
                let year;

                @if (isset($vacation))
                    let startDate = $('#start_date_form').val();
                    year = startDate ? new Date(startDate + 'T00:00:00').getFullYear() : new Date()
                        .getFullYear();
                @else
                    year = new Date().getFullYear();
                @endif

                $.get("{{ route('admin.vacations.personnel-info') }}", {
                    personnel_id: id,
                    year: year
                }, function(data) {
                    $('#days-info').text('Días usados: ' + data.used_days +
                        ' | Días disponibles: ' + data.available_days);

                    @if (!isset($vacation))
                        $('#requested_days').attr('max', data.available_days);
                    @endif
                });
            } else {
                $('#days-info').text('');
            }
        }

        $('#personnel_search_input').off('keyup.vacationSearch focus.vacationSearch')
            .on('keyup.vacationSearch focus.vacationSearch', function() {
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

        $(document).off('click.vacationPersonnelOption', '.personnel-option')
            .on('click.vacationPersonnelOption', '.personnel-option', function() {
                let id = $(this).data('id');
                let text = $(this).data('text');

                $('#personnel_id').val(id);
                $('#personnel_search_input').val(text);
                $('#personnel_results').addClass('d-none');
                $('#clear_personnel_search').show();

                updateDaysInfo();
            });

        $(document).off('click.personnelResultsVacations')
            .on('click.personnelResultsVacations', function(e) {
                if (!$(e.target).closest('#personnel_search_input, #personnel_results').length) {
                    $('#personnel_results').addClass('d-none');
                }
            });

        function resetPersonnelSearch() {
            $('#personnel_id').val('');
            $('#personnel_search_input').val('');
            $('#personnel_results').addClass('d-none');
            $('#clear_personnel_search').hide();
            $('#days-info').text('');
        }

        $('#clear_personnel_search').off('click.vacationClear')
            .on('click.vacationClear', function() {
                resetPersonnelSearch();
            });

        $('#personnel_search_input').off('input.vacationClearButton')
            .on('input.vacationClearButton', function() {
                $('#personnel_id').val('');

                if ($(this).val().trim() !== '') {
                    $('#clear_personnel_search').show();
                } else {
                    resetPersonnelSearch();
                }
            });

        @if (isset($vacation))
            $('#start_date_form').off('change.vacationDaysInfo')
                .on('change.vacationDaysInfo', updateDaysInfo);

            updateDaysInfo();
        @endif

        $('#start_date_form, #requested_days').off('change.vacationCalc input.vacationCalc')
            .on('change.vacationCalc input.vacationCalc', function() {
                let startStr = $('#start_date_form').val();
                let days = parseInt($('#requested_days').val());

                if (startStr && days > 0) {
                    let start = new Date(startStr + 'T00:00:00');
                    start.setDate(start.getDate() + days - 1);

                    let day = ("0" + start.getDate()).slice(-2);
                    let month = ("0" + (start.getMonth() + 1)).slice(-2);
                    let year = start.getFullYear();

                    $('#end_date_display').val(day + '/' + month + '/' + year);
                } else {
                    $('#end_date_display').val('');
                }
            });

        if ($('#personnel_search_input').val().trim() !== '') {
            $('#clear_personnel_search').show();
        }
    });
</script>
