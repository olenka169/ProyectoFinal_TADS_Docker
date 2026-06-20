<form action="{{ route('admin.personnel-groups.store') }}"
      method="POST">

    @csrf

    @include('admin.personnel-groups.template.form')

    <div class="text-right">

        <button type="submit"
                class="btn btn-success">

            <i class="fas fa-save"></i>
            Guardar

        </button>

        <button type="button"
                class="btn btn-secondary"
                data-dismiss="modal">

            Cancelar

        </button>

    </div>

</form>