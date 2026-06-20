<form action="{{ route('admin.vehicle-types.update', $type->id) }}" method="POST">
    @csrf
    @method('PUT')

    @include('admin.vehicle-types.template.form')

    <button type="submit" class="btn btn-success btn-sm">
        <i class="fas fa-pen"></i> Actualizar
    </button>

    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">
        <i class="fas fa-times"></i> Cancelar
    </button>
</form>
