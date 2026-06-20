@extends('adminlte::page')

@section('title', 'Zonas')

@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)

@section('content')

    <div class="pt-3"></div>

    <div class="card">
        <div class="card-header">
            <button type="button" class="btn btn-primary btn-sm float-right" id="btnNuevo">
                <i class="fas fa-plus"></i> Nueva Zona
            </button>

            <button type="button" class="btn btn-success btn-sm float-right mr-2" id="btnMapaGeneral">
                <i class="fas fa-map-marked-alt"></i> Ver Mapa de Zonas
            </button>

            <h4>
                <i class="fas fa-map-marker-alt"></i>
                Lista de Zonas
            </h4>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-striped table-hover table-sm text-nowrap" id="datatable">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Distrito</th>
                        <th>Provincia</th>
                        <th>Departamento</th>
                        <th>Descripción</th>
                        <th>Coordenadas</th>
                        <th>Estado</th>
                        <th>Creación</th>
                        <th width="120">Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="modal fade" id="formModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">Formulario de Zona</h5>

                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body"></div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css">
@stop

@section('js')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@turf/turf@6/turf.min.js"></script>

    <script>
        let table;

        $(document).ready(function() {
            table = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                autoWidth: false,
                order: [
                    [0, 'asc']
                ],
                ajax: "{{ route('admin.zones.index') }}",
                columns: [{
                        data: "name",
                        name: "zones.name"
                    },
                    {
                        data: "district",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: "province",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: "department",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: "description",
                        name: "zones.description"
                    },
                    {
                        data: "coordinates_status",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: "status_label",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: "created_at_formatted",
                        name: "zones.created_at"
                    },
                    {
                        data: "actions",
                        orderable: false,
                        searchable: false
                    }
                ],
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
                }
            });
        });

        $('#btnNuevo').on('click', function() {
            $.get("{{ route('admin.zones.create') }}", function(response) {
                $('#modalTitle').html('<i class="fas fa-map-marker-alt"></i> Nueva Zona');
                $('#formModal .modal-body').html(response);
                $('#formModal').modal('show');
            });
        });

        $('#btnMapaGeneral').on('click', function() {
            $.get("{{ route('admin.zones.general-map') }}", function(response) {
                $('#modalTitle').html('<i class="fas fa-map"></i> Mapa General de Zonas');
                $('#formModal .modal-body').html(response);
                $('#formModal').modal('show');
            });
        });

        $(document).on('click', '.btn-editar', function() {
            let id = $(this).attr('id');

            $.get("{{ route('admin.zones.edit', ':id') }}".replace(':id', id), function(response) {
                $('#modalTitle').html('<i class="fas fa-pen"></i> Editar Zona');
                $('#formModal .modal-body').html(response);
                $('#formModal').modal('show');
            });
        });

        $(document).on('submit', '#zoneForm', function(e) {
            e.preventDefault();
            window.saveZone();
        });

        $('#formModal').on('shown.bs.modal', function() {
            if ($('#zoneMap').length) {
                initZoneLeafletMap();
            }
        });

        $(document).on('click', '.btn-ver-mapa', function() {
            let id = $(this).attr('id');

            $.get("{{ route('admin.zones.show', ':id') }}".replace(':id', id), function(response) {
                $('#modalTitle').html('<i class="fas fa-map"></i> Mapa de la Zona');
                $('#formModal .modal-body').html(response);
                $('#formModal').modal('show');
            });
        });


        window.saveZone = function() {
            let form = $('#zoneForm');
            let coordinates = $('#coordinates').val();

            let parsedCoordinates = [];

            try {
                parsedCoordinates = JSON.parse(coordinates);
            } catch (e) {
                parsedCoordinates = [];
            }

            if (parsedCoordinates.length < 3) {
                Swal.fire('Error', 'Debe registrar mínimo 3 coordenadas para definir el perímetro.', 'error');
                return;
            }

            if (window.hasOverlapWithExistingZones && window.hasOverlapWithExistingZones(parsedCoordinates)) {
                Swal.fire(
                    'Zona no permitida',
                    'No puede guardar una zona que se superpone con una zona ya registrada.',
                    'warning'
                );
                return;
            }

            let formData = new FormData(form[0]);
            formData.set('coordinates', coordinates);

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'Accept': 'application/json'
                },
                success: function(response) {
                    $('#formModal').modal('hide');
                    $('#datatable').DataTable().ajax.reload(null, false);
                    Swal.fire('Correcto', response.message, 'success');
                },
                error: function(xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        let errors = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                        Swal.fire('Error', errors, 'error');
                    } else {
                        Swal.fire('Error', xhr.responseJSON?.message ?? 'No se pudo guardar.', 'error');
                    }
                }
            });
        }

        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault();

            let url = $(this).data('url');

            Swal.fire({
                title: "¿Está seguro de eliminar?",
                text: "Esta acción es irreversible",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed || result.value) {
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            $('#datatable').DataTable().ajax.reload(null, false);

                            Swal.fire(
                                'Proceso exitoso',
                                response.message,
                                'success'
                            );
                        },
                        error: function(xhr) {
                            let response = xhr.responseJSON;

                            Swal.fire(
                                'Ocurrió un error',
                                response ? response.message : 'No se pudo eliminar',
                                'error'
                            );
                        }
                    });
                }
            });
        });

        function initZoneLeafletMap() {
            if (window.zoneLeafletMap) {
                window.zoneLeafletMap.remove();
                window.zoneLeafletMap = null;
            }

            let drawnItems = new L.FeatureGroup();
            let currentPolygon = null;
            let existingZonesLayer = new L.FeatureGroup();

            let existingZonesData = [];

            window.zoneLeafletMap = L.map('zoneMap').setView([-6.7630, -79.8366], 15);
            window.zoneLeafletMap.addLayer(existingZonesLayer);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(window.zoneLeafletMap);

            window.zoneLeafletMap.addLayer(drawnItems);

            let drawControl = new L.Control.Draw({
                draw: {
                    polygon: {
                        allowIntersection: false,
                        showArea: true,
                        shapeOptions: {
                            color: 'blue'
                        }
                    },
                    marker: false,
                    circle: false,
                    rectangle: false,
                    polyline: false,
                    circlemarker: false
                },
                edit: {
                    featureGroup: drawnItems,
                    remove: true
                }
            });

            window.zoneLeafletMap.addControl(drawControl);

            function getCoordinates() {
                let coordinates = [];

                $('.coordinate-row').each(function() {
                    let lat = $(this).find('.coord-lat').val();
                    let lng = $(this).find('.coord-lng').val();

                    if (lat !== '' && lng !== '') {
                        coordinates.push({
                            lat: parseFloat(lat),
                            lng: parseFloat(lng)
                        });
                    }
                });

                return coordinates;
            }

            function setHiddenCoordinates() {
                let coordinates = getCoordinates();
                $('#coordinates').val(JSON.stringify(coordinates));
            }

            function renderCoordinateRows(coordinates) {
                $('#coordinatesRows').html('');

                coordinates.forEach(function(coord) {
                    addCoordinateRow(coord.lat, coord.lng);
                });

                setHiddenCoordinates();
            }

            function addCoordinateRow(lat = '', lng = '') {
                let row = `
            <div class="input-group mb-2 coordinate-row">
                <input type="number" step="any" class="form-control coord-lat"
                    placeholder="Latitud" value="${lat}">

                <input type="number" step="any" class="form-control coord-lng"
                    placeholder="Longitud" value="${lng}">

                <div class="input-group-append">
                    <button type="button" class="btn btn-danger btn-remove-coordinate">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;

                $('#coordinatesRows').append(row);
                setHiddenCoordinates();
            }

            function leafletCoordsToTurfPolygon(coords) {
                let points = coords.map(c => [c.lng, c.lat]);

                if (points.length > 0) {
                    points.push(points[0]);
                }

                return turf.polygon([points]);
            }

            function hasOverlapWithExistingZones(coordinates) {
                if (coordinates.length < 3) {
                    return false;
                }

                let currentPolygonTurf = leafletCoordsToTurfPolygon(coordinates);

                for (let zone of existingZonesData) {
                    if (!zone.coordinates || zone.coordinates.length < 3) {
                        continue;
                    }

                    let existingPolygonTurf = leafletCoordsToTurfPolygon(zone.coordinates);

                    if (turf.booleanIntersects(currentPolygonTurf, existingPolygonTurf)) {
                        return true;
                    }
                }

                return false;
            }

            function drawPolygonFromRows() {
                let coordinates = getCoordinates();

                drawnItems.clearLayers();
                currentPolygon = null;

                if (coordinates.length >= 3) {

                    if (hasOverlapWithExistingZones(coordinates)) {
                        Swal.fire(
                            'Zona no permitida',
                            'El perímetro seleccionado se superpone con una zona ya registrada.',
                            'warning'
                        );

                        setHiddenCoordinates();
                        return;
                    }

                    let latlngs = coordinates.map(c => [c.lat, c.lng]);

                    currentPolygon = L.polygon(latlngs, {
                        color: 'blue',
                        fillColor: 'blue',
                        fillOpacity: 0.18
                    });

                    drawnItems.addLayer(currentPolygon);
                    enableCurrentPolygonEditing(currentPolygon);
                    window.zoneLeafletMap.fitBounds(currentPolygon.getBounds());
                }

                setHiddenCoordinates();
            }

            function updateRowsFromPolygon(layer) {
                let latlngs = layer.getLatLngs()[0];

                let coordinates = latlngs.map(function(point) {
                    return {
                        lat: point.lat,
                        lng: point.lng
                    };
                });

                renderCoordinateRows(coordinates);
            }

            let savedCoordinates = $('#coordinates').val();

            if (savedCoordinates && savedCoordinates !== 'null' && savedCoordinates !== '[]') {
                try {
                    let coords = JSON.parse(savedCoordinates);

                    if (coords.length > 0) {
                        renderCoordinateRows(coords);
                        drawPolygonFromRows();
                    }
                } catch (e) {
                    $('#coordinates').val('');
                }
            } else {
                addCoordinateRow();
            }

            window.zoneLeafletMap.on(L.Draw.Event.CREATED, function(event) {
                drawnItems.clearLayers();

                let layer = event.layer;
                let latlngs = layer.getLatLngs()[0];

                let coordinates = latlngs.map(function(point) {
                    return {
                        lat: point.lat,
                        lng: point.lng
                    };
                });

                if (hasOverlapWithExistingZones(coordinates)) {
                    Swal.fire(
                        'Zona no permitida',
                        'El perímetro seleccionado se superpone con una zona ya registrada.',
                        'warning'
                    );
                    return;
                }

                drawnItems.addLayer(layer);
                currentPolygon = layer;

                updateRowsFromPolygon(layer);
                enableCurrentPolygonEditing(layer);
            });

            window.zoneLeafletMap.on(L.Draw.Event.EDITED, function(event) {
                event.layers.eachLayer(function(layer) {
                    let latlngs = layer.getLatLngs()[0];

                    let coordinates = latlngs.map(function(point) {
                        return {
                            lat: point.lat,
                            lng: point.lng
                        };
                    });

                    if (hasOverlapWithExistingZones(coordinates)) {
                        Swal.fire(
                            'Zona no permitida',
                            'El perímetro editado se superpone con una zona ya registrada.',
                            'warning'
                        );

                        drawPolygonFromRows();
                        return;
                    }

                    currentPolygon = layer;
                    updateRowsFromPolygon(layer);
                });
            });

            window.zoneLeafletMap.on(L.Draw.Event.DELETED, function() {
                $('#coordinatesRows').html('');
                addCoordinateRow();
                $('#coordinates').val('');
                currentPolygon = null;
            });

            $(document).off('click', '#btnAddCoordinate').on('click', '#btnAddCoordinate', function() {
                addCoordinateRow();
            });

            $(document).off('click', '.btn-remove-coordinate').on('click', '.btn-remove-coordinate', function() {
                $(this).closest('.coordinate-row').remove();

                if ($('.coordinate-row').length === 0) {
                    addCoordinateRow();
                }

                drawPolygonFromRows();
            });

            $(document).off('input', '.coord-lat, .coord-lng').on('input', '.coord-lat, .coord-lng', function() {
                drawPolygonFromRows();
            });

            $(document).off('click', '#btnClearPolygon').on('click', '#btnClearPolygon', function() {
                Swal.fire({
                    title: '¿Limpiar todo?',
                    text: 'Se eliminarán todas las coordenadas, el polígono y la búsqueda actual.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, limpiar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed || result.value) {
                        drawnItems.clearLayers();
                        $('#coordinatesRows').html('');
                        addCoordinateRow();
                        $('#coordinates').val('');
                        $('#searchAddress').val('');
                        currentPolygon = null;
                    }
                });
            });

            function loadExistingZones() {
                let currentZoneId = $('#zoneForm').data('zone-id') || '';

                $.get("{{ route('admin.zones.polygons', ':id') }}".replace(':id', currentZoneId), function(zones) {
                    existingZonesData = zones;
                    existingZonesLayer.clearLayers();

                    zones.forEach(function(zone) {
                        if (!zone.coordinates || zone.coordinates.length < 3) {
                            return;
                        }

                        let latlngs = zone.coordinates.map(c => [c.lat, c.lng]);

                        let polygon = L.polygon(latlngs, {
                            color: 'red',
                            fillColor: 'red',
                            fillOpacity: 0.18,
                            weight: 2,
                            interactive: false
                        });

                        polygon.bindTooltip(zone.name, {
                            permanent: false,
                            direction: 'center'
                        });

                        existingZonesLayer.addLayer(polygon);
                    });
                });
            }

            function enableCurrentPolygonEditing(layer) {
                if (layer && layer.editing) {
                    layer.editing.enable();

                    layer.on('edit', function() {
                        updateRowsFromPolygon(layer);
                    });
                }
            }

            window.hasOverlapWithExistingZones = hasOverlapWithExistingZones;

            loadExistingZones();

            setTimeout(function() {
                window.zoneLeafletMap.invalidateSize();
            }, 300);


        }

        $('#formModal').on('hidden.bs.modal', function() {
            if (window.generalZonesMapInstance) {
                window.generalZonesMapInstance.remove();
                window.generalZonesMapInstance = null;
            }

            $('#formModal .modal-body').html('');
        });
    </script>
@endsection
