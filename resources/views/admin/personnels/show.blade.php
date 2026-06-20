<div class="row">
    <div class="col-md-4 text-center">
        @if ($personnel->photo_path)
            <img src="{{ asset('storage/' . $personnel->photo_path) }}" class="img-thumbnail"
                style="width:220px;height:220px;object-fit:cover;">
        @else
            <div class="bg-light d-flex align-items-center justify-content-center border rounded mx-auto"
                style="width:220px;height:220px;">
                <i class="fas fa-image fa-4x text-muted"></i>
            </div>
        @endif
    </div>

    <div class="col-md-8">
        <table class="table table-bordered table-sm">
            <tr>
                <th>DNI</th>
                <td>{{ $personnel->dni }}</td>
            </tr>
            <tr>
                <th>Tipo de personal</th>
                <td>{{ $personnel->type->name ?? 'Sin tipo' }}</td>
            </tr>

            @if (strtolower($personnel->type->name ?? '') == 'conductor')
                <tr>
                    <th>N° de licencia</th>
                    <td>
                        {{ $personnel->license_number ?: 'No registrada' }}
                    </td>
                </tr>
            @endif

            <tr>
                <th>Nombres</th>
                <td>{{ $personnel->names }}</td>
            </tr>
            <tr>
                <th>Apellidos</th>
                <td>{{ $personnel->lastnames }}</td>
            </tr>
            <tr>
                <th>Fecha de nacimiento</th>
                <td>{{ \Carbon\Carbon::parse($personnel->birthdate)->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <th>Teléfono</th>
                <td>{{ $personnel->phone ?: '—' }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $personnel->email }}</td>
            </tr>
            <tr>
                <th>Dirección</th>
                <td>{{ $personnel->address }}</td>
            </tr>
            <tr>
                <th>Estado</th>
                <td>
                    @if ($personnel->status == 'Activo')
                        <span class="badge badge-success px-2 py-1" style="font-size: 0.9rem;">Activo</span>
                    @else
                        <span class="badge badge-danger px-2 py-1" style="font-size: 0.9rem;">Inactivo</span>
                    @endif
                </td>
            </tr>

        </table>
    </div>
</div>
