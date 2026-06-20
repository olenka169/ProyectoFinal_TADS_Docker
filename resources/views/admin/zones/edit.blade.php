<form action="{{ route('admin.zones.update', $zone->id) }}" method="POST" id="zoneForm"
    data-zone-id="{{ $zone->id }}">
    @csrf
    @method('PUT')

    @include('admin.zones.template.form')

    <button type="submit" class="btn btn-success btn-sm">
        <i class="fas fa-pen"></i> Actualizar
    </button>

    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">
        <i class="fas fa-times"></i> Cancelar
    </button>
</form>
