<div class="form-group">

    <label for="name">
        Nombre del Grupo
    </label>

    <input type="text"
           id="name"
           name="name"
           class="form-control"
           value="{{ $group->name ?? '' }}"
           required>

</div>

<div class="form-group">

    <label for="zone_id">
        Zona
    </label>

    <select id="zone_id"
            name="zone_id"
            class="form-control"
            required>

        <option value="">
            Seleccione...
        </option>

        @foreach($zones as $zone)

            <option value="{{ $zone->id }}"
                @isset($group)
                    {{ $group->zone_id == $zone->id ? 'selected' : '' }}
                @endisset>

                {{ $zone->name }}

            </option>

        @endforeach

    </select>

</div>

<div class="form-group">

    <label for="shift_id">
        Turno
    </label>

    <select id="shift_id"
            name="shift_id"
            class="form-control"
            required>

        <option value="">
            Seleccione...
        </option>

        @foreach($shifts as $shift)

            <option value="{{ $shift->id }}"
                @isset($group)
                    {{ $group->shift_id == $shift->id ? 'selected' : '' }}
                @endisset>

                {{ $shift->name }}

            </option>

        @endforeach

    </select>

</div>

<div class="form-group">

    <label for="vehicle_id">
        Vehículo
    </label>

    <select id="vehicle_id"
            name="vehicle_id"
            class="form-control"
            required>

        <option value="">
            Seleccione...
        </option>

        @foreach($vehicles as $vehicle)

            <option value="{{ $vehicle->id }}"
                    data-capacity="{{ $vehicle->passenger_capacity }}"
                @isset($group)
                    {{ $group->vehicle_id == $vehicle->id ? 'selected' : '' }}
                @endisset>

                {{ $vehicle->plate }}
                - Capacidad: {{ $vehicle->passenger_capacity }}

            </option>

        @endforeach

    </select>

</div>

<hr>

<div class="form-group">

    <label>
        Días de trabajo
    </label>

    @php

    $dias = [
        'Lunes',
        'Martes',
        'Miércoles',
        'Jueves',
        'Viernes',
        'Sábado',
        'Domingo'
    ];

    $diasSeleccionados = [];

    if(isset($group))
    {
        $diasSeleccionados =
            $group->workdays
                  ->pluck('day')
                  ->toArray();
    }

    @endphp

    <div class="row">

        @foreach($dias as $dia)

        <div class="col-md-4">

            <div class="form-check">

                <input type="checkbox"
                       class="form-check-input"
                       name="workdays[]"
                       value="{{ $dia }}"
                       id="dia_{{ $loop->index }}"

                       {{ in_array($dia,$diasSeleccionados)
                            ? 'checked'
                            : '' }}>

                <label class="form-check-label"
                       for="dia_{{ $loop->index }}">

                    {{ $dia }}

                </label>

            </div>

        </div>

        @endforeach

    </div>

    <small class="text-muted">
        Seleccione los días en que trabajará este grupo.
    </small>

</div>

<hr>

<div class="form-group">

    <label for="driver_id">
        Conductor
    </label>

    <select id="driver_id"
            name="driver_id"
            class="form-control"
            required>

        <option value="">
            Seleccione conductor
        </option>

        @foreach($drivers as $driver)

            @php
                $contractType = $driver->activeContract ? $driver->activeContract->type : 'Sin contrato';
            @endphp

            <option value="{{ $driver->id }}"
                @isset($group)
                    {{ $group->driver_id == $driver->id ? 'selected' : '' }}
                @endisset>

                {{ $driver->names }}
                {{ $driver->lastnames }}
                ({{ $contractType }})

            </option>

        @endforeach

    </select>

</div>

<hr>

<div id="helpers-container"></div>

<hr>

<div class="form-group">

    <label for="status">
        Estado
    </label>

    <select id="status"
            name="status"
            class="form-control">

        <option value="1"
            @isset($group)
                {{ $group->status ? 'selected' : '' }}
            @else
                selected
            @endisset>

            Activo

        </option>

        <option value="0"
            @isset($group)
                {{ !$group->status ? 'selected' : '' }}
            @endisset>

            Inactivo

        </option>

    </select>

</div>

@php

$helpersJson = $helpers->map(function ($helper) {

    $contractType = $helper->activeContract ? $helper->activeContract->type : 'Sin contrato';

    return [
        'id' => $helper->id,
        'name' => $helper->names . ' ' . $helper->lastnames . ' (' . $contractType . ')',
    ];

})->values()->toArray();

$selectedHelpersJson = [];

if (isset($group)) {

    $selectedHelpersJson = $group->helpers
        ->pluck('personnel_id')
        ->values()
        ->toArray();
}

@endphp

<script type="application/json" id="helpers-data">
{!! json_encode($helpersJson) !!}
</script>

<script type="application/json" id="selected-helpers-data">
{!! json_encode($selectedHelpersJson) !!}
</script>

<script>

var helpers = JSON.parse(
    document.getElementById('helpers-data').textContent
);

var selectedHelpers = JSON.parse(
    document.getElementById('selected-helpers-data').textContent
);

function generarCamposAyudantes()
{
    let capacity = parseInt(
        $('#vehicle_id option:selected').data('capacity')
    ) || 3; // Valor por defecto de 3 (1 conductor + 2 ayudantes) si no hay selección

    let cantidadAyudantes = Math.max(capacity - 1, 0);

    // Asegurar que siempre se muestren al menos 2 campos si no hay vehículo seleccionado
    if (!$('#vehicle_id').val() && cantidadAyudantes < 2) {
        cantidadAyudantes = 2;
    }

    let html = '';

    for(let i = 0; i < cantidadAyudantes; i++)
    {
        html += `
            <div class="form-group">

                <label>
                    Ayudante ${i + 1}
                </label>

                <select
                    name="helpers[]"
                    class="form-control"
                    required>

                    <option value="">
                        Seleccione ayudante
                    </option>
        `;

        helpers.forEach(function(helper){

            let selected = '';

            if (
                selectedHelpers.length > i &&
                selectedHelpers[i] == helper.id
            ) {
                selected = 'selected';
            }

            html += `
                <option value="${helper.id}" ${selected}>
                    ${helper.name}
                </option>
            `;
        });

        html += `
                </select>

            </div>
        `;
    }

    $('#helpers-container').html(html);
}

$(document).off('change', '#vehicle_id');

$(document).on('change', '#vehicle_id', function() {

    generarCamposAyudantes();

});

setTimeout(function() {

    generarCamposAyudantes();

}, 200);

</script>