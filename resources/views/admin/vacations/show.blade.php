<div class="row">
    <div class="col-md-12">

        <table class="table table-bordered table-sm">
            <tr>
                <th width="35%">DNI</th>
                <td>{{ $vacation->personnel->dni }}</td>
            </tr>

            <tr>
                <th>Personal</th>
                <td>
                    {{ $vacation->personnel->names }}
                    {{ $vacation->personnel->lastnames }}
                </td>
            </tr>

            <tr>
                <th>Fecha de inicio</th>
                <td>{{ $vacation->start_date->format('d/m/Y') }}</td>
            </tr>

            <tr>
                <th>Fecha de fin</th>
                <td>{{ $vacation->end_date->format('d/m/Y') }}</td>
            </tr>

            <tr>
                <th>Días solicitados</th>
                <td>{{ $vacation->requested_days }} días</td>
            </tr>

            <tr>
                <th>Estado</th>
                <td>
                    @php
                        $badges = [
                            'Pendiente' => 'warning',
                            'Aprobada' => 'success',
                            'Rechazada' => 'danger',
                        ];

                        $color = $badges[$vacation->status] ?? 'secondary';
                    @endphp

                    <span class="badge badge-{{ $color }} px-2 py-1" style="font-size: 0.9rem;">
                        {{ $vacation->status }}
                    </span>
                </td>
            </tr>

            <tr>
                <th>Año correspondiente</th>
                <td>{{ $vacation->start_date->year }}</td>
            </tr>

            <tr>
                <th>Días disponibles</th>
                <td>
                    <strong>{{ $availableDays }} días</strong>
                </td>
            </tr>

            <tr>
                <th>Notas</th>
                <td>{{ $vacation->notes ?: 'Sin notas adicionales' }}</td>
            </tr>
        </table>

        <div class="alert alert-info mb-0">
            <i class="fas fa-info-circle"></i>
            Las vacaciones solo aplican para personal con contrato activo de tipo
            <strong>Permanente</strong> o <strong>Nombrado</strong>.
        </div>

    </div>
</div>

<div class="text-right mt-3">
    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">
        <i class="fas fa-times"></i> Cerrar
    </button>
</div>
