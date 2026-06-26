/**
 * admin_home.js — Lógica del formulario de edición del HOME
 *
 * Ruta:   /Administrador/admin_home.js
 *
 * Flujo:
 *  1. Al cargar la página → GET a api_home.php → pobla todos los campos.
 *  2. Al cambiar la imagen → muestra preview local sin guardar.
 *  3. Al hacer submit     → POST con FormData (multipart) a api_home.php.
 *
 * Sincronización: La respuesta del GET siempre refleja la BD más reciente;
 * no se usa caché local (localStorage) para los datos corporativos.
 */

/* ── Ruta de la API (relativa al panel admin) ───────────────────────── */
var API_HOME = '../PHP/api_home.php';

/* ── Carpeta de imágenes donde el servidor guarda los logos ─────────── */
var DIR_LOGOS = '../imagenes_empresa/';

/* ══════════════════════════════════════════════════════════════════════
 *  Inicialización al cargar la página
 * ═════════════════════════════════════════════════════════════════════ */
window.addEventListener('DOMContentLoaded', function () {
    cargarDatosHome();
    inicializarPreviewLogo();
    marcarMenuActivo();
});

/* ══════════════════════════════════════════════════════════════════════
 *  1. Cargar datos actuales desde la BD (GET)
 * ═════════════════════════════════════════════════════════════════════ */
function cargarDatosHome() {
    fetch(API_HOME, { cache: 'no-store' }) // no-store garantiza datos frescos
        .then(function (res) { return res.json(); })
        .then(function (resp) {
            if (!resp.success || !resp.data) {
                console.warn('api_home: sin datos o error en la respuesta.');
                return;
            }
            poblarFormulario(resp.data);
        })
        .catch(function (err) {
            console.error('Error al cargar datos del HOME:', err);
        });
}

/**
 * poblarFormulario — Rellena cada campo con el valor de la BD.
 * Los nombres de campo deben coincidir exactamente con las columnas de empresa_home.
 */
function poblarFormulario(d) {
    // ── Identidad corporativa
    setVal('nombre_empresa',    d.nombre_empresa);
    setVal('ano_fundacion',     d.ano_fundacion);
    setVal('giro_comercial',    d.giro_comercial);
    setVal('titulo_bienvenida', d.titulo_bienvenida);
    setVal('subtitulo',         d.subtitulo);

    // ── Institucional
    setVal('mision',  d.mision);
    setVal('vision',  d.vision);

    // ── Contacto
    setVal('email',     d.email);
    setVal('telefono1', d.telefono1);
    setVal('telefono2', d.telefono2);

    // ── Dirección
    setVal('calle_num', d.calle_num);
    setVal('colonia',   d.colonia);
    setVal('ciudad',    d.ciudad);
    setVal('estado',    d.estado);
    setVal('cp',        d.cp);

    // ── Logo actual (preview + campo oculto de persistencia)
    if (d.logo_imagen) {
        var preview = document.getElementById('preview-logo');
        if (preview) {
            preview.src = DIR_LOGOS + d.logo_imagen;
        }
        var nombreActual = document.getElementById('nombre-logo-actual');
        if (nombreActual) {
            nombreActual.textContent = d.logo_imagen + ' (actual)';
        }
        /*
         * PERSISTENCIA CRÍTICA:
         * Poblar el hidden input con el nombre actual de la imagen.
         * Si el usuario guarda sin subir un logo nuevo, el servidor
         * leerá este valor y conservará logo_imagen sin modificarlo.
         */
        var hiddenLogo = document.getElementById('logo_actual');
        if (hiddenLogo) {
            hiddenLogo.value = d.logo_imagen;
        }
    }

    // ── Fecha de última actualización
    if (d.actualizado_en) {
        var txt = document.getElementById('txt-ultimo-update');
        if (txt) {
            txt.textContent = '🕒 Última actualización: ' + d.actualizado_en;
        }
    }
}

/** Asigna un valor a un campo por id, solo si el campo existe y el valor no es nulo. */
function setVal(id, valor) {
    var el = document.getElementById(id);
    if (el && valor !== null && valor !== undefined) {
        el.value = valor;
    }
}

/* ══════════════════════════════════════════════════════════════════════
 *  2. Preview del logo al seleccionar un archivo local
 * ═════════════════════════════════════════════════════════════════════ */
function inicializarPreviewLogo() {
    var inputLogo = document.getElementById('logo_imagen');
    if (!inputLogo) return;

    inputLogo.addEventListener('change', function () {
        var archivo = this.files[0];
        if (!archivo) return;

        // Validación básica del lado cliente (el servidor la repite con rigor)
        var tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!tiposPermitidos.includes(archivo.type)) {
            alert('❌ Solo se permiten imágenes JPG, PNG, GIF o WebP.');
            this.value = '';
            return;
        }
        if (archivo.size > 5 * 1024 * 1024) {
            alert('❌ El logo no debe superar 5 MB.');
            this.value = '';
            return;
        }

        // Mostrar preview local de la imagen seleccionada
        var reader = new FileReader();
        reader.onload = function (e) {
            var preview = document.getElementById('preview-logo');
            if (preview) preview.src = e.target.result;
        };
        reader.readAsDataURL(archivo);

        // Actualizar texto del nombre
        var nombreEl = document.getElementById('nombre-logo-actual');
        if (nombreEl) {
            nombreEl.textContent = archivo.name + ' (nuevo — pendiente de guardar)';
        }
    });
}

/* ══════════════════════════════════════════════════════════════════════
 *  3. Submit del formulario → POST con FormData
 * ═════════════════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('homeForm');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        var btn = document.getElementById('btn-guardar');
        var msg = document.getElementById('mensaje');

        btn.textContent = '⏳ Guardando…';
        btn.disabled    = true;
        msg.style.display = 'none';

        /*
         * FormData recoge automáticamente todos los campos del formulario,
         * incluyendo el archivo de logo si el usuario seleccionó uno.
         * Esto permite enviar multipart/form-data, requerido para subida de archivos.
         */
        var formData = new FormData(form);

        fetch(API_HOME, {
            method: 'POST',
            body:   formData
            // No establecer Content-Type: el navegador lo asigna con el boundary correcto
        })
        .then(function (res) { return res.json(); })
        .then(function (resp) {
            btn.textContent = '💾 Guardar Cambios';
            btn.disabled    = false;

            msg.style.display = 'block';

            if (resp.success) {
                msg.className   = 'msg-success';
                msg.textContent = '✅ ' + resp.message;

                // Recargar preview si se cambió el logo (actualiza nombre-logo-actual)
                if (resp.logo_guardado) {
                    // Refrescar los datos para obtener el nuevo nombre de archivo
                    cargarDatosHome();
                }

                // Ocultar el mensaje de éxito después de 5 s
                setTimeout(function () { msg.style.display = 'none'; }, 5000);
            } else {
                msg.className   = 'msg-error';
                msg.textContent = '❌ ' + (resp.error || 'No se pudo guardar. Intente de nuevo.');
            }
        })
        .catch(function (err) {
            console.error('Error de red al guardar HOME:', err);
            btn.textContent = '💾 Guardar Cambios';
            btn.disabled    = false;
            msg.style.display = 'block';
            msg.className   = 'msg-error';
            msg.textContent = '❌ Error de conexión. Verifique su red e intente de nuevo.';
        });
    });
});

/* ══════════════════════════════════════════════════════════════════════
 *  4. Marcar enlace activo en el sidebar
 * ═════════════════════════════════════════════════════════════════════ */
function marcarMenuActivo() {
    var links = document.querySelectorAll('.sidebar-nav a');
    for (var i = 0; i < links.length; i++) {
        if (links[i].textContent.indexOf('Editar HOME') !== -1) {
            links[i].classList.add('activo');
        } else {
            links[i].classList.remove('activo');
        }
    }
}
