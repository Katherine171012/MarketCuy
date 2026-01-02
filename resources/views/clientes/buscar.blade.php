{{-- PANEL QUE SE DESPLIEGA - ID debe coincidir con el botón --}}
<div class="collapse mb-4 {{ (isset($busquedaActiva) || request('valor_texto') || request('valor_ciudad')) ? 'show' : '' }}" id="panelBusqueda">
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden" style="border: 1px solid #660404 !important;">
        <div class="card-header py-2 text-white" style="background-color: #660404;">
            <span class="fw-bold small text-uppercase"><i class="fas fa-search me-2"></i>Buscar por Parámetro</span>
        </div>
        <div class="card-body p-4 bg-white">
            <form method="POST" action="{{ route('clientes.buscar') }}">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Criterio:</label>
                        <select name="campo" id="criterioBusqueda" class="form-select form-select-sm border-0 bg-light shadow-sm">
                            <option value="cli_nombre" data-ph="Ingrese el nombre..." {{ request('campo') == 'cli_nombre' ? 'selected' : '' }}>Nombre</option>
                            <option value="cli_ruc_ced" data-ph="Ingrese Cédula o RUC..." {{ request('campo') == 'cli_ruc_ced' ? 'selected' : '' }}>Cédula / RUC</option>
                            <option value="cli_mail" data-ph="Ingrese el correo..." {{ request('campo') == 'cli_mail' ? 'selected' : '' }}>Correo</option>
                            <option value="id_ciudad" data-ph="Seleccione la ciudad..." {{ request('campo') == 'id_ciudad' ? 'selected' : '' }}>Ciudad</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label id="labelDinamico" class="form-label small fw-bold text-muted text-uppercase">Ingrese el nombre del cliente</label>

                        <input type="text" name="valor_texto" id="inputTexto"
                               class="form-control form-control-sm border-0 bg-light shadow-sm {{ request('campo') == 'id_ciudad' ? 'd-none' : '' }}"
                               value="{{ request('valor_texto') }}" placeholder="...">

                        <select name="valor_ciudad" id="selectCiudad"
                                class="form-select form-select-sm border-0 bg-light shadow-sm {{ request('campo') == 'id_ciudad' ? '' : 'd-none' }}">
                            <option value="">-- Seleccione Ciudad --</option>
                            @foreach($ciudades as $ciu)
                                <option value="{{ trim($ciu->id_ciudad) }}" {{ request('valor_ciudad') == trim($ciu->id_ciudad) ? 'selected' : '' }}>
                                    {{ $ciu->ciu_descripcion }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-concho btn-sm fw-bold px-4 w-100 shadow-sm">Buscar</button>
                            {{-- Botón para cerrar el panel --}}
                            <button type="button" class="btn btn-secondary btn-sm fw-bold px-3 shadow-sm" data-bs-toggle="collapse" data-bs-target="#panelBusqueda">Cerrar</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Lógica para que el buscador sea dinámico
    document.getElementById('criterioBusqueda').addEventListener('change', function() {
        const ph = this.options[this.selectedIndex].getAttribute('data-ph');
        document.getElementById('labelDinamico').innerText = ph;

        if (this.value === 'id_ciudad') {
            document.getElementById('inputTexto').classList.add('d-none');
            document.getElementById('selectCiudad').classList.remove('d-none');
        } else {
            document.getElementById('selectCiudad').classList.add('d-none');
            document.getElementById('inputTexto').classList.remove('d-none');
        }
    });
</script>
