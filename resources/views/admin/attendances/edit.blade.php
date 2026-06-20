<form action="{{ route('admin.attendances.update', $attendance->id) }}" method="POST">
    @csrf
    @method('PUT')

    @include('admin.attendances.template.form')

    <button type="submit" class="btn btn-success btn-sm">
        <i class="fas fa-pen"></i> Actualizar
    </button>

    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">
        <i class="fas fa-times"></i> Cancelar
    </button>
</form>
