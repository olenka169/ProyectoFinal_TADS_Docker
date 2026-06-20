<form action="{{ route('admin.colors.store') }}" method="POST">
    @csrf

    @include('admin.colors.template.form')

    <button type="submit" class="btn btn-success btn-sm">
        <i class="fas fa-save"></i> Guardar
    </button>

    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">
        <i class="fas fa-times"></i> Cancelar
    </button>
</form>
