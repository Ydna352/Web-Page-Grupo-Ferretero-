/* ============================================================
   script.js — Lógica JavaScript del Panel de Administración
   ============================================================

   ¿Qué hace este archivo?
   Genera dinámicamente las "tarjetas" de módulos en el panel.
   En lugar de escribir cada tarjeta a mano en el HTML, usamos
   JavaScript para crearlas desde un arreglo (lista) de datos.

   Esto es exactamente lo que React hacía con .map() en el JSX.
   Aquí lo hacemos con un ciclo "for" clásico.

   El archivo se carga al final del HTML para que ya existan
   todos los elementos antes de que JS intente encontrarlos.
   ============================================================ */


/* ============================================================
   SECCIÓN 1 — ÍCONOS SVG
   ============================================================
   Los íconos originales venían de la librería "lucide-react".
   Aquí los reemplazamos con SVG puros (texto XML que dibuja
   íconos en el navegador). Cada ícono es una función que
   devuelve el código SVG como texto.

   ¿Qué es un SVG?
   Es un formato de imagen vectorial. En lugar de guardar
   píxeles, guarda instrucciones matemáticas: "dibuja una línea
   de aquí a allá". Por eso siempre se ve nítido sin importar
   el tamaño.
   ============================================================ */

/**
 * Devuelve el SVG del ícono de personas (Trabajadores)
 * Equivalente a: <Users /> de lucide-react
 */
function iconoTrabajadores() {
    return `<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
        <circle cx="9" cy="7" r="4"/>
        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
    </svg>`;
}

/**
 * Devuelve el SVG del ícono de camión (Proveedores)
 * Equivalente a: <Truck /> de lucide-react
 */
function iconoProveedores() {
    return `<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <rect x="1" y="3" width="15" height="13"/>
        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
        <circle cx="5.5" cy="18.5" r="2.5"/>
        <circle cx="18.5" cy="18.5" r="2.5"/>
    </svg>`;
}

/**
 * Devuelve el SVG del ícono de paquete (Suministros)
 * Equivalente a: <Package /> de lucide-react
 */
function iconoSuministros() {
    return `<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <line x1="16.5" y1="9.4" x2="7.5" y2="4.21"/>
        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
        <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
        <line x1="12" y1="22.08" x2="12" y2="12"/>
    </svg>`;
}

/**
 * Devuelve el SVG del ícono de documento (Facturas)
 * Equivalente a: <FileText /> de lucide-react
 */
function iconoFacturas() {
    return `<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
        <polyline points="14 2 14 8 20 8"/>
        <line x1="16" y1="13" x2="8" y2="13"/>
        <line x1="16" y1="17" x2="8" y2="17"/>
        <polyline points="10 9 9 9 8 9"/>
    </svg>`;
}

/**
 * Devuelve el SVG del ícono de tendencia (Ventas)
 * Equivalente a: <TrendingUp /> de lucide-react
 */
function iconoVentas() {
    return `<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
        <polyline points="17 6 23 6 23 12"/>
    </svg>`;
}

/**
 * Devuelve el SVG del ícono de cajas (Materiales)
 * Equivalente a: <Boxes /> de lucide-react
 */
function iconoMateriales() {
    return `<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path d="M2.97 12.92A2 2 0 0 0 2 14.63v3.24a2 2 0 0 0 .97 1.71l3 1.8a2 2 0 0 0 2.06 0L12 19v-5.5l-5-3-4.03 2.42Z"/>
        <path d="m7 16.5-4.74-2.85"/>
        <path d="m7 16.5 5-3"/>
        <path d="M7 16.5v5.17"/>
        <path d="M12 13.5V19l3.97 2.38a2 2 0 0 0 2.06 0l3-1.8a2 2 0 0 0 .97-1.71v-3.24a2 2 0 0 0-.97-1.71L17 10.5l-5 3Z"/>
        <path d="m17 16.5-5-3"/>
        <path d="m17 16.5 4.74-2.85"/>
        <path d="M17 16.5v5.17"/>
        <path d="M7.97 4.42A2 2 0 0 0 7 6.13v4.37l5 3 5-3V6.13a2 2 0 0 0-.97-1.71l-3-1.8a2 2 0 0 0-2.06 0l-3 1.8Z"/>
        <path d="M12 8 7.26 5.15"/>
        <path d="m12 8 4.74-2.85"/>
        <path d="M12 13.5V8"/>
    </svg>`;
}

function iconoEditarHome() {
    return `<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
    </svg>`;
}

/* ============================================================
   SECCIÓN 2 — DATOS DE LOS MÓDULOS
   ============================================================
   Aquí definimos la "lista" de módulos igual que en React.
   En React era: const modules = [ {...}, {...}, ... ]
   En JS puro es exactamente lo mismo, un arreglo de objetos.

   ¿Qué es un arreglo? Es una lista de elementos entre [ ]
   ¿Qué es un objeto? Es un conjunto de propiedades entre { }
   ============================================================ */
var modulos = [
    {
        titulo: 'Gestión de Trabajadores',
        descripcion: 'CRUD completo de empleados',
        icono: iconoTrabajadores,  /* Función que genera el SVG */
        ruta: 'workers.php',
        color: '#3e8cdd'           /* Azul */
    },
    {
        titulo: 'Gestión de Clientes',
        descripcion: 'Administra tus clientes y compras',
        icono: iconoTrabajadores,
        ruta: 'clientes.php',
        color: '#74cb3c'           /* Verde */
    },
    {
        titulo: 'Gestión de Proveedores',
        descripcion: 'Administra tus proveedores',
        icono: iconoProveedores,
        ruta: 'proveedores.php',
        color: '#74cb3c'           /* Verde */
    },
    {
        titulo: 'Gestión de Suministros',
        descripcion: 'Control de suministros y pedidos',
        icono: iconoSuministros,
        ruta: 'supplies.php',
        color: '#3e8cdd'
    },
    {
        titulo: 'Gestión de Facturas',
        descripcion: 'Consulta y gestiona facturas',
        icono: iconoFacturas,
        /*
           NAVEGACIÓN REAL: apunta al archivo HTML de facturas.
           Antes era '/admin/invoices' (React Router, no funciona en local).
        */
        ruta: 'admin_facturas.php',
        color: '#74cb3c'
    },
    {
        titulo: 'Gestión de Ventas',
        descripcion: 'Reportes y control de ventas',
        icono: iconoVentas,
        ruta: 'ventas.php',
        color: '#3e8cdd'
    },
    {
        titulo: 'Gestión de Materiales',
        descripcion: 'Control de inventario',
        icono: iconoMateriales,
        /*
           NAVEGACIÓN REAL: usamos la ruta relativa al archivo inventory.php
           que está en la misma carpeta que dashboard.php.
           En React era: path: '/admin/inventory' (React Router)
           Aquí es simplemente el nombre del archivo HTML.
        */
        ruta: 'inventory.php',
        color: '#74cb3c'
    },

    {
        titulo: 'Editar HOME',
        descripcion: 'Edita el contenido de la página principal',
        icono: iconoEditarHome,
        ruta: 'admin_home.html',
        color: '#3e8cdd'   // azul, o '#74cb3c' si prefieres verde
    }
];


/* ============================================================
   SECCIÓN 3 — FUNCIÓN PARA CREAR UNA TARJETA
   ============================================================
   Esta función recibe los datos de UN módulo y devuelve
   el código HTML de su tarjeta como texto (string).

   En React, esto era el JSX dentro del .map():
     <Link to={module.path}>
       <Card style={{ borderColor: module.color }}>
         ...
       </Card>
     </Link>

   Aquí lo hacemos con template literals (texto entre comillas
   invertidas ` `) que permiten insertar variables con ${...}
   ============================================================ */

/**
 * Crea el HTML de una tarjeta de módulo.
 *
 * @param {Object} modulo - El objeto con los datos del módulo
 * @param {string} modulo.titulo      - Nombre del módulo
 * @param {string} modulo.descripcion - Descripción corta
 * @param {Function} modulo.icono     - Función que devuelve el SVG
 * @param {string} modulo.ruta        - URL a la que navega al hacer clic
 * @param {string} modulo.color       - Color hex del módulo (#abc123)
 * @returns {string} Código HTML completo de la tarjeta
 */
function crearTarjeta(modulo) {
    /* Llamamos a la función del ícono para obtener el SVG */
    var svgIcono = modulo.icono();

    /*
       Usamos template literals (` `) para construir el HTML.
       Las variables se insertan entre ${ }
       Esto es como "armar" el HTML dinámicamente.
    */
    return `
        <a href="${modulo.ruta}" class="enlace-card">
            <div class="card" style="border-color: ${modulo.color};">

                <!-- Cuadro del ícono con el color del módulo -->
                <div class="card-icono" style="background-color: ${modulo.color};">
                    ${svgIcono}
                </div>

                <!-- Título con el color del módulo -->
                <div class="card-titulo" style="color: ${modulo.color};">
                    ${modulo.titulo}
                </div>

                <!-- Descripción en gris -->
                <div class="card-descripcion">
                    ${modulo.descripcion}
                </div>

                <!-- Texto de llamada a la acción -->
                <div class="card-cta">
                    Haz clic para acceder
                </div>

            </div>
        </a>
    `;
}


/* ============================================================
   SECCIÓN 4 — FUNCIÓN PRINCIPAL: GENERAR LAS TARJETAS
   ============================================================
   Esta función recorre todos los módulos y los "pinta" en la
   página web dentro del contenedor con id="grilla-modulos".

   En React esto era el .map() devolviendo JSX.
   Aquí usamos un ciclo for clásico igual al que se usa en los
   archivos de la carpeta "temas" de tus ejercicios de clase.
   ============================================================ */

/**
 * Genera y muestra todas las tarjetas en la grilla.
 * Se llama automáticamente cuando carga la página.
 */
function generarModulos() {
    /*
       document.getElementById('grilla-modulos')
       → Busca en el HTML el elemento cuyo id sea "grilla-modulos"
       → Es equivalente a seleccionar una "caja" del HTML para
         poner contenido adentro.
    */
    var contenedor = document.getElementById('grilla-modulos');

    /* Empezamos con texto vacío */
    var htmlTarjetas = '';

    /*
       Ciclo for: recorre el arreglo "modulos" de principio a fin.
       - i = 0         → Empieza en el primer elemento (índice 0)
       - i < modulos.length → Continúa mientras no llegue al final
       - i++           → Aumenta i en 1 cada vez (siguiente módulo)
    */
    for (var i = 0; i < modulos.length; i++) {
        /* modulos[i] = el módulo actual en esta vuelta del ciclo */
        htmlTarjetas = htmlTarjetas + crearTarjeta(modulos[i]);
    }

    /*
       innerHTML permite "inyectar" HTML dentro de un elemento.
       Es como decirle al navegador: "el contenido de esta caja
       ahora es este nuevo HTML".
    */
    contenedor.innerHTML = htmlTarjetas;
}


/* ============================================================
   SECCIÓN 5 — FUNCIÓN PARA MARCAR ENLACE ACTIVO EN SIDEBAR
   ============================================================
   Al hacer clic en un enlace del sidebar, marca ese enlace
   como "activo" cambiando su clase CSS.

   Equivale al comportamiento de React Router que destacaba
   el enlace de la página actual.
   ============================================================ */

/**
 * Marca un enlace del sidebar como activo.
 * @param {HTMLElement} elementoClicado - El enlace que se clicó
 */
function marcarActivo(elementoClicado) {
    /* Primero quitamos la clase 'activo' de TODOS los enlaces */
    var todosLosEnlaces = document.querySelectorAll('.sidebar-nav a');

    for (var i = 0; i < todosLosEnlaces.length; i++) {
        todosLosEnlaces[i].classList.remove('activo');
    }

    /* Luego agregamos 'activo' SOLO al enlace que se clicó */
    elementoClicado.classList.add('activo');
}


/* ============================================================
   SECCIÓN 6 — PUNTO DE ENTRADA
   ============================================================
   window.onload = function() {...}
   Esto le dice al navegador: "cuando TERMINES de cargar toda la
   página, ejecuta este código".

   Es importante porque JS no puede encontrar elementos HTML
   que todavía no existen en la página.

   En React esto era equivalente al renderizado del componente
   al montar en el DOM.
   ============================================================ */
window.onload = function () {
    /* Genera e inserta todas las tarjetas de módulos */
    generarModulos();

    /* Marcamos "Dashboard" como activo por defecto en el sidebar */
    var primerEnlace = document.querySelector('.sidebar-nav a');
    if (primerEnlace) {
        primerEnlace.classList.add('activo');
    }
};


/* ============================================================
   cerrarSesion — Simula cierre de sesión
   ============================================================ */
function cerrarSesion() {
    window.location.href = '../Home/Home.html';
}

