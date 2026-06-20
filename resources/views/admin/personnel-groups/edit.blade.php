<form action="{{ route('admin.personnel-groups.update', $group->id) }}"
      method="POST">

    @csrf
    @method('PUT')

    @include('admin.personnel-groups.template.form')

    <div class="text-right">

        <button type="submit"
                class="btn btn-success">

            <i class="fas fa-save"></i>
            Actualizar

        </button>

        <button type="button"
                class="btn btn-secondary"
                data-dismiss="modal">

            Cancelar

        </button>

    </div>

</form>