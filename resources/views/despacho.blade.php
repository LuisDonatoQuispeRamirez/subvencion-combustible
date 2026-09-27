<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Despacho de combustible</title>
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,600;0,700;1,700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="{{ asset('css/despacho.css') }}">
</head>
<body>
    <header class="topbar">
        <div class="topbar-brand">
            <i data-lucide="fuel"></i>
            <h1>CONSULTA Y REGISTRO DE DESPACHO</h1>
        </div>
        <div class="topbar-user">
            <i data-lucide="user-round"></i>
            <span>{{ auth('estacion')->user()->nombre }}</span>
            <form method="POST" action="/logout" style="margin-left:16px;">
                @csrf
                <button type="submit" class="topbar-logout"><i data-lucide="log-out"></i> Salir</button>
            </form>
        </div>
    </header>

    <div class="contenedor">
        <div class="columna-form">
            <label><i data-lucide="car-front"></i> Placa</label>
            <div class="fila-placa">
                <input type="text" id="placa">
                <button type="button" class="btn-camara" onclick="abrirCamara()"><i data-lucide="camera"></i></button>
            </div>
            <div id="camaraPanel" class="camara-panel oculto">
                <select id="selectCamara" onchange="cambiarCamara()"></select>
                <video id="video" autoplay playsinline></video>
                <canvas id="canvas" class="oculto"></canvas>
                <div class="camara-acciones">
                    <button type="button" onclick="capturarFoto()"><i data-lucide="scan-line"></i> Capturar</button>
                    <button type="button" class="btn-secundario" onclick="cerrarCamara()"><i data-lucide="x"></i> Cancelar</button>
                </div>
                <p id="estadoOcr" style="font-size:13px;color:var(--ink-soft);margin:8px 0 0;"></p>
            </div>

            <input type="hidden" id="estacion" value="{{ auth('estacion')->id() }}">

            <button onclick="consultarCupo()"><i data-lucide="search"></i> Consultar cupo</button>

            <hr class="separador">

            <label><i data-lucide="fuel"></i> Litros a despachar</label>
            <input type="number" id="litros" step="0.01">
            <button onclick="registrarDespacho()"><i data-lucide="file-text"></i> Registrar despacho</button>
            <button type="button" onclick="limpiarFormulario()"><i data-lucide="circle-plus"></i> Nueva consulta</button>
        </div>

        <div class="columna-resultados">
            <div id="panelConsulta" class="panel panel-vacio">Datos del vehículo</div>
            <div id="panelRegistro" class="panel panel-vacio">Resultado del despacho</div>
        </div>
    </div>

    <script>
        function mostrarPanel(el, html) {
            el.classList.remove('oculto', 'panel-vacio');
            el.innerHTML = html;
            lucide.createIcons();
        }

        let streamCamara = null;

        async function abrirCamara() {
            const panel = document.getElementById('camaraPanel');
            panel.classList.remove('oculto');

            const permisoTemp = await navigator.mediaDevices.getUserMedia({ video: true });
            permisoTemp.getTracks().forEach(track => track.stop());

            const dispositivos = await navigator.mediaDevices.enumerateDevices();
            const camaras = dispositivos.filter(d => d.kind === 'videoinput');

            const select = document.getElementById('selectCamara');
            select.innerHTML = '';
            camaras.forEach((cam, i) => {
                const opcion = document.createElement('option');
                opcion.value = cam.deviceId;
                opcion.textContent = cam.label || ('Cámara ' + (i + 1));
                select.appendChild(opcion);
            });

            iniciarStream(select.value);
        }

        async function iniciarStream(deviceId) {
            document.getElementById('video').classList.remove('oculto');
            document.getElementById('canvas').classList.add('oculto');
            if (streamCamara) {
                streamCamara.getTracks().forEach(track => track.stop());
           }
           const video = document.getElementById('video');
           try {
                streamCamara = await navigator.mediaDevices.getUserMedia({
                    video: deviceId
                        ? { deviceId: { exact: deviceId }, width: { ideal: 1280 }, height: { ideal: 720 } }
                        : { width: { ideal: 1280 }, height: { ideal: 720 } }
                });
                video.srcObject = streamCamara;
            } catch (err) {
                document.getElementById('estadoOcr').innerText = 'No se pudo acceder a la cámara: ' + err.message;
            }
        }

        function cambiarCamara() {
            const select = document.getElementById('selectCamara');
            iniciarStream(select.value);
        }

        function cerrarCamara() {
            if (streamCamara) {
                streamCamara.getTracks().forEach(track => track.stop());
                streamCamara = null;
            }
            document.getElementById('camaraPanel').classList.add('oculto');
            document.getElementById('estadoOcr').innerText = '';
        }

        async function capturarFoto() {
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            const ctx = canvas.getContext('2d');
            const datosImagen = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const pixeles = datosImagen.data;
            const totalPixeles = pixeles.length / 4;

            let sumaGris = 0;
            for (let i = 0; i < pixeles.length; i += 4) {
                sumaGris += pixeles[i] * 0.3 + pixeles[i + 1] * 0.59 + pixeles[i + 2] * 0.11;
            }
            const umbral = sumaGris / totalPixeles;

            let pixelesBlancos = 0;
            for (let i = 0; i < pixeles.length; i += 4) {
                const gris = pixeles[i] * 0.3 + pixeles[i + 1] * 0.59 + pixeles[i + 2] * 0.11;
                const valor = gris > umbral ? 255 : 0;
                pixeles[i] = pixeles[i + 1] = pixeles[i + 2] = valor;
                if (valor === 255) pixelesBlancos++;
            }

            if (pixelesBlancos < totalPixeles / 2) {
                for (let i = 0; i < pixeles.length; i += 4) {
                    pixeles[i] = pixeles[i + 1] = pixeles[i + 2] = 255 - pixeles[i];
                }
             }

            ctx.putImageData(datosImagen, 0, 0);

            video.classList.add('oculto');
            canvas.classList.remove('oculto');

            const estado = document.getElementById('estadoOcr');
            estado.innerText = 'Leyendo la placa...';

            const worker = await Tesseract.createWorker('eng');
            await worker.setParameters({
                tessedit_char_whitelist: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
                tessedit_pageseg_mode: '6'
            });
            const resultado = await worker.recognize(canvas);
            await worker.terminate();

            const textoLimpio = resultado.data.text.toUpperCase().replace(/[^A-Z0-9]/g, '');
            const coincidencia = textoLimpio.match(/[0-9]{3,4}[A-Z]{3}/);

            if (coincidencia) {
                const placaDetectada = coincidencia[0];
                const inputPlaca = document.getElementById('placa');
                inputPlaca.value = placaDetectada;
                inputPlaca.dispatchEvent(new Event('input'));
                estado.innerText = 'Placa detectada: ' + placaDetectada;
                cerrarCamara();
                consultarCupo();
            } else {
                estado.innerText = 'No se pudo leer bien la placa, inténtalo de nuevo o escríbela a mano.';
                video.classList.remove('oculto');
                 canvas.classList.add('oculto');
            }
        }

        const nombresUso = {
            particular: 'Particular',
            transporte_publico: 'Transporte público',
            agricola: 'Agrícola',
            carga: 'Carga'
        };

        function fechaProximoMes() {
            const hoy = new Date();
            const proximo = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 1);
            return proximo.toLocaleDateString('es-BO', { day: 'numeric', month: 'long', year: 'numeric' });
        }

        function limpiarFormulario() {
            document.getElementById('placa').value = '';
            document.getElementById('litros').value = '';

            const panelConsulta = document.getElementById('panelConsulta');
            panelConsulta.className = 'panel panel-vacio';
            panelConsulta.innerText = 'Los datos del vehículo aparecerán aquí';

            const panelRegistro = document.getElementById('panelRegistro');
            panelRegistro.className = 'panel panel-vacio';
            panelRegistro.innerText = 'El resultado del despacho aparecerá aquí';
        }

        async function consultarCupo() {
            const placa = document.getElementById('placa').value;
            const panel = document.getElementById('panelConsulta');

            const respuesta = await fetch('/api/consultar-cupo', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
                body: JSON.stringify({ placa: placa })
            });
            const datos = await respuesta.json();

            if (datos.estado === 'ok') {
                mostrarPanel(panel, `
                    <div class="panel-header">
                        <span>Datos del vehículo y cupo</span>
                        <span class="badge badge-ok"><i data-lucide="circle-check"></i>Disponible</span>
                    </div>
                    <div class="ficha">
    <img src="${datos.foto_url || ''}" onerror="this.style.display='none'">

    <div class="ficha-info">
        <div class="placa-visual">
            <div class="placa-pais">BOLIVIA</div>
            <div class="placa-numero">${datos.placa}</div>
        </div>

        <strong>${datos.marca || ''} ${datos.modelo || ''}</strong>

        <div class="tags">
            <span class="tag">${nombresUso[datos.tipo_uso] || datos.tipo_uso}</span>
            <span class="tag">
                <i data-lucide="droplet"></i>${datos.tipo_combustible || ''}
            </span>
        </div>
    </div>
</div>
                    <div class="metricas">
                    <div class="metrica">
                        <i data-lucide="fuel" class="metrica-icono"></i>
                        <span class="num">${datos.litros_asignados}</span>
                        <span class="lbl">Asignado</span>
                    </div>
                    <div class="metrica">
                        <i data-lucide="droplet" class="metrica-icono"></i>
                        <span class="num">${datos.litros_consumidos}</span>
                        <span class="lbl">Consumido</span>
                    </div>
                        <div class="metrica">
                            <i data-lucide="circle-check" class="metrica-icono"></i>
                            <span class="num">${datos.litros_disponibles}</span>
                            <span class="lbl">Disponible</span>
                        </div>
                    </div>
                `);
            } else if (datos.estado === 'no_encontrado') {
                mostrarPanel(panel, `<div class="panel-header"><span>Datos del vehículo y cupo</span><span class="badge badge-error"><i data-lucide="circle-alert"></i>No encontrado</span></div><p>No existe ningún vehículo con esa placa.</p>`);
            } else if (datos.estado === 'sin_cupo') {
                mostrarPanel(panel, `<div class="panel-header"><span>Datos del vehículo y cupo</span><span class="badge badge-error"><i data-lucide="circle-alert"></i>Sin cupo</span></div><p>Este vehículo no tiene cupo para el mes actual.</p>`);
            } else {
                mostrarPanel(panel, `<div class="panel-header"><span>Datos del vehículo y cupo</span><span class="badge badge-error"><i data-lucide="circle-alert"></i>Placa inválida</span></div><p>${(datos.errors && datos.errors.placa) ? datos.errors.placa[0] : 'Revisa el formato de la placa.'}</p>`);
            }
        }

        async function registrarDespacho() {
            const placa = document.getElementById('placa').value;
            const estacion_id = document.getElementById('estacion').value;
            const litros = document.getElementById('litros').value;
            const panel = document.getElementById('panelRegistro');

            const respuesta = await fetch('/api/registrar-despacho', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
                body: JSON.stringify({ placa: placa, estacion_id: estacion_id, litros: litros })
            });
            const datos = await respuesta.json();

            if (datos.estado === 'registrado') {
                mostrarPanel(panel, `<div class="panel-header"><span>Resultado del despacho</span><span class="badge badge-ok"><i data-lucide="circle-check"></i>Registrado</span></div><p>Quedan ${datos.litros_disponibles} litros disponibles este mes.</p>`);
            } else if (datos.estado === 'cupo_excedido') {
                mostrarPanel(panel, `
                    <div class="panel-header"><span>Resultado del despacho</span><span class="badge badge-error"><i data-lucide="circle-alert"></i>Cupo excedido</span></div>
                    <p>Solo hay ${datos.litros_disponibles} litros disponibles, no se puede despachar esa cantidad.</p>
                    <div class="aviso-fecha"><i data-lucide="calendar-days"></i> El cupo se renueva el ${fechaProximoMes()}</div>
                `);
            } else if (datos.estado === 'no_encontrado') {
                mostrarPanel(panel, `<div class="panel-header"><span>Resultado del despacho</span><span class="badge badge-error"><i data-lucide="circle-alert"></i>No encontrado</span></div><p>No existe ningún vehículo con esa placa.</p>`);
            } else if (datos.estado === 'sin_cupo') {
                mostrarPanel(panel, `<div class="panel-header"><span>Resultado del despacho</span><span class="badge badge-error"><i data-lucide="circle-alert"></i>Sin cupo</span></div><p>Este vehículo no tiene cupo para el mes actual.</p>`);
            } else {
                mostrarPanel(panel, `<div class="panel-header"><span>Resultado del despacho</span><span class="badge badge-error"><i data-lucide="circle-alert"></i>Error</span></div><p>Revisa los datos ingresados.</p>`);
            }
        }

        lucide.createIcons();
    </script>
</body>
</html>