<form action="{{ route('admin.vacations.update', $vacation->id) }}" method="POST">

    @csrf
    @method('PUT')

    @include('admin.vacations.template.form')

    <button type="submit" class="btn btn-success btn-sm">
        <i class="fas fa-pen"></i> Actualizar
    </button>

    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">
        <i class="fas fa-times"></i> Cancelar
    </button>

</form>
