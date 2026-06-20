<div class="row">
    <div class="col-md-4">

        <div class="card shadow-sm mb-2">
            <div class="card-header bg-secondary text-white">
                <strong>
                    <i class="fas fa-filter"></i> Filtros de búsqueda
                </strong>
            </div>

            <div class="card-body py-2">
                <div class="form-group mb-2">
                    <label class="mb-1">Departamento</label>
                    <select id="filter_department_id" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-2">
                    <label class="mb-1">Provincia</label>
                    <select id="filter_province_id" class="form-control form-control-sm">
                        <option value="">Todas</option>
                    </select>
                </div>

                <div class="form-group mb-0">
                    <label class="mb-1">Distrito</label>
                    <select id="filter_district_id" class="form-control form-control-sm">
                        <option value="">Todos</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-2">
            <div class="card-header bg-light py-2">
                <strong>
                    <i class="fas fa-chart-bar"></i>
                    Resumen de zonas
                </strong>
            </div>

            <div class="card-body py-2">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="border rounded p-2 bg-light h-100 d-flex flex-column justify-content-center">
                            <i class="fas fa-map-marked-alt text-primary"></i>
                            <h5 class="mb-0 mt-1" id="totalZonesBox" style="font-size: 1rem; line-height: 1.2;">
                                0
                            </h5>
                            <small class="text-muted">Zonas</small>
                        </div>
                    </div>

                    <div class="col-4">
                        <div class="border rounded p-2 bg-light h-100 d-flex flex-column justify-content-center">
                            <i class="fas fa-check-circle text-success"></i>
                            <h5 class="mb-0 mt-1" id="activeZonesBox" style="font-size: 1rem; line-height: 1.2;">
                                0
                            </h5>
                            <small class="text-muted">Activas</small>
                        </div>
                    </div>

                    <div class="col-4">
                        <div class="border rounded p-2 bg-light h-100 d-flex flex-column justify-content-center">
                            <i class="fas fa-map-pin text-info"></i>
                            <h5 class="mb-0 mt-1" id="totalPointsBox" style="font-size: 1rem; line-height: 1.2;">
                                0
                            </h5>
                            <small class="text-muted">Puntos</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-light py-2">
                <strong>
                    <i class="fas fa-list"></i>
                    Leyenda del mapa
                </strong>
            </div>

            <div class="card-body p-0">
                <div id="zonesLegend" class="table-responsive" style="max-height: 210px; overflow-y:auto;">
                    <div class="text-muted text-center py-3">
                        Cargando zonas...
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="col-md-8">

        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <strong>
                    Visualización general de zonas
                </strong>
            </div>

            <div class="card-body py-2">
                <div id="generalZonesMap"
                    style="width: 100%; height: 570px; border: 1px solid #dee2e6; border-radius: 4px;">
                </div>

                <small class="text-muted d-block mt-2">
                    <i class="fas fa-info-circle"></i>
                    <span id="zoneCounterText">Cada color representa una zona registrada diferente.</span>
                </small>
            </div>
        </div>

    </div>
</div>

<script>
    setTimeout(function() {
        if (window.generalZonesMapInstance) {
            window.generalZonesMapInstance.remove();
            window.generalZonesMapInstance = null;
        }

        let map = L.map('generalZonesMap').setView([-9.19, -75.0152], 6);
        window.generalZonesMapInstance = map;

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        let colors = [
            '#007bff',
            '#28a745',
            '#dc3545',
            '#ffc107',
            '#17a2b8',
            '#6f42c1',
            '#fd7e14',
            '#20c997',
            '#e83e8c',
            '#343a40'
        ];

        let allZones = [];
        let zonesLayer = L.featureGroup().addTo(map);

        let defaultDepartmentName = 'Lambayeque';
        let defaultProvinceName = 'Chiclayo';
        let defaultDistrictName = 'José Leonardo Ortiz';

        $.get("{{ route('admin.zones.all-polygons') }}", function(zones) {
            allZones = zones;

            setDefaultLocationFilters();
        });

        $('#filter_department_id').off('change.generalMap').on('change.generalMap', function() {
            let departmentId = $(this).val();

            $('#filter_province_id').html('<option value="">Todas</option>');
            $('#filter_district_id').html('<option value="">Todos</option>');

            if (departmentId) {
                $.get("{{ route('admin.zones.provinces', ':id') }}".replace(':id', departmentId),
                    function(provinces) {
                        provinces.forEach(function(province) {
                            $('#filter_province_id').append(
                                `<option value="${province.id}">${province.name}</option>`
                            );
                        });
                    });
            }

            renderZones();
            centerMapByFilter();
        });

        $('#filter_province_id').off('change.generalMap').on('change.generalMap', function() {
            let provinceId = $(this).val();

            $('#filter_district_id').html('<option value="">Todos</option>');

            if (provinceId) {
                $.get("{{ route('admin.zones.districts', ':id') }}".replace(':id', provinceId),
                    function(districts) {
                        districts.forEach(function(district) {
                            $('#filter_district_id').append(
                                `<option value="${district.id}">${district.name}</option>`
                            );
                        });
                    });
            }

            renderZones();
            centerMapByFilter();
        });

        $('#filter_district_id').off('change.generalMap').on('change.generalMap', function() {
            renderZones();
            centerMapByFilter();
        });

        function setDefaultLocationFilters() {
            let departmentOption = $('#filter_department_id option').filter(function() {
                return $(this).text().trim().toLowerCase() === defaultDepartmentName.toLowerCase();
            });

            if (!departmentOption.length) {
                renderZones();
                return;
            }

            let departmentId = departmentOption.val();

            $('#filter_department_id').val(departmentId);

            $.get("{{ route('admin.zones.provinces', ':id') }}".replace(':id', departmentId), function(
                provinces) {
                $('#filter_province_id').html('<option value="">Todas</option>');
                $('#filter_district_id').html('<option value="">Todos</option>');

                provinces.forEach(function(province) {
                    $('#filter_province_id').append(
                        `<option value="${province.id}">${province.name}</option>`
                    );
                });

                let provinceOption = $('#filter_province_id option').filter(function() {
                    return $(this).text().trim().toLowerCase() === defaultProvinceName
                        .toLowerCase();
                });

                if (!provinceOption.length) {
                    renderZones();
                    return;
                }

                let provinceId = provinceOption.val();

                $('#filter_province_id').val(provinceId);

                $.get("{{ route('admin.zones.districts', ':id') }}".replace(':id', provinceId),
                    function(districts) {
                        $('#filter_district_id').html('<option value="">Todos</option>');

                        districts.forEach(function(district) {
                            $('#filter_district_id').append(
                                `<option value="${district.id}">${district.name}</option>`
                            );
                        });

                        let districtOption = $('#filter_district_id option').filter(function() {
                            return $(this).text().trim().toLowerCase() ===
                                defaultDistrictName.toLowerCase();
                        });

                        if (districtOption.length) {
                            $('#filter_district_id').val(districtOption.val());
                        }

                        renderZones();
                        centerMapByFilter();
                    });
            });
        }

        function getFilteredZones() {
            let departmentId = $('#filter_department_id').val();
            let provinceId = $('#filter_province_id').val();
            let districtId = $('#filter_district_id').val();

            return allZones.filter(function(zone) {
                if (!zone.coordinates || zone.coordinates.length < 3) {
                    return false;
                }

                if (departmentId && zone.department_id != departmentId) {
                    return false;
                }

                if (provinceId && zone.province_id != provinceId) {
                    return false;
                }

                if (districtId && zone.district_id != districtId) {
                    return false;
                }

                return true;
            });
        }

        function renderZones() {
            zonesLayer.clearLayers();

            let zones = getFilteredZones();
            let totalZones = 0;
            let activeZones = 0;
            let totalPoints = 0;
            let legendHtml = '';

            zones.forEach(function(zone, index) {
                totalZones++;
                totalPoints += zone.coordinates.length;

                if (zone.status) {
                    activeZones++;
                }

                let color = colors[index % colors.length];

                let latlngs = zone.coordinates.map(function(point) {
                    return [point.lat, point.lng];
                });

                let polygon = L.polygon(latlngs, {
                    color: color,
                    fillColor: color,
                    fillOpacity: 0.25,
                    weight: 3
                }).addTo(zonesLayer);

                polygon.bindPopup(`
                    <div style="text-align:center; min-width:170px;">
                        <div style="font-size:22px; color:${color}; margin-bottom:5px;">
                            <i class="fas fa-map-marked-alt"></i>
                        </div>

                        <h6 style="font-weight:bold; margin-bottom:8px;">
                            ${zone.name}
                        </h6>

                        <div style="font-size:13px;">
                            <div>
                                <i class="fas fa-map-marker-alt text-muted"></i>
                                ${zone.district}
                            </div>

                            <div>
                                <i class="fas fa-city text-muted"></i>
                                ${zone.province}
                            </div>

                            <div>
                                <i class="fas fa-trash-alt text-muted"></i>
                                Residuos: ${zone.average_waste ? zone.average_waste + ' kg' : 'N/A'}
                            </div>

                            <div>
                                <span class="badge badge-${zone.status ? 'success' : 'danger'} mt-1">
                                    ${zone.status ? 'Activo' : 'Inactivo'}
                                </span>
                            </div>
                        </div>
                    </div>
                `);

                polygon.bindTooltip(zone.name, {
                    permanent: false,
                    direction: 'center'
                });

                legendHtml += `
                    <div class="d-flex align-items-center px-2 py-2 border-bottom">
                        <span style="
                            width:14px;
                            height:14px;
                            background:${color};
                            display:inline-block;
                            border-radius:3px;
                            margin-right:8px;
                            flex-shrink:0;">
                        </span>

                        <div style="line-height:1.2;">
                            <strong style="font-size: 0.85rem;">${zone.name}</strong><br>
                            <small class="text-muted">${zone.district}</small>
                        </div>
                    </div>
                `;
            });

            $('#totalZonesBox').text(totalZones);
            $('#activeZonesBox').text(activeZones);
            $('#totalPointsBox').text(totalPoints);

            $('#zoneCounterText').text(totalZones + ' zona(s) encontradas según el filtro seleccionado.');

            $('#zonesLegend').html(
                legendHtml ||
                `<div class="text-center text-muted py-3">
                    No hay zonas registradas para el filtro seleccionado.
                </div>`
            );

            if (zonesLayer.getLayers().length > 0) {
                map.fitBounds(zonesLayer.getBounds(), {
                    padding: [20, 20]
                });
            }
        }

        function centerMapByFilter() {
            let department = $('#filter_department_id option:selected').text().trim();
            let province = $('#filter_province_id option:selected').text().trim();
            let district = $('#filter_district_id option:selected').text().trim();

            let queryParts = [];

            if (district && district !== 'Todos') {
                queryParts.push(district);
            }

            if (province && province !== 'Todas') {
                queryParts.push(province);
            }

            if (department && department !== 'Todos') {
                queryParts.push(department);
            }

            if (queryParts.length === 0) {
                return;
            }

            queryParts.push('Perú');

            $.get('https://nominatim.openstreetmap.org/search', {
                q: queryParts.join(', '),
                format: 'json',
                limit: 1
            }, function(response) {
                if (response.length > 0) {
                    let lat = parseFloat(response[0].lat);
                    let lon = parseFloat(response[0].lon);

                    map.setView(
                        [lat, lon],
                        district !== 'Todos' ? 14 : province !== 'Todas' ? 11 : 8
                    );
                }
            });
        }

        setTimeout(function() {
            map.invalidateSize();
        }, 300);
    }, 300);
</script>
