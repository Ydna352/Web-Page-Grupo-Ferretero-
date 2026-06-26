<?php require_once '../PHP/auth.php'; auth_require_rol('admin'); ?>
<!DOCTYPE html>
<!--
    ============================================================
    index.html — Panel de Administración
    ============================================================

    ¿Qué es este archivo?
    Es la página principal del panel de administración.
    Equivale al componente AdminDashboard.tsx de React.

    Estructura de la página:
    ┌─────────────────────────────────────────────────┐
    │  HEADER (barra superior)                        │
    ├──────────┬──────────────────────────────────────┤
    │          │                                      │
    │ SIDEBAR  │   ÁREA DEL DASHBOARD                 │
    │ (menú   │   (título + 6 tarjetas de módulos)   │
    │ lateral) │                                      │
    │          │                                      │
    └──────────┴──────────────────────────────────────┘

    Archivos que usa este HTML:
    - styles.css  → Le da el "look" visual a la página
    - script.js   → Le da el comportamiento interactivo
    ============================================================
-->
<html lang="es">

<head>
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="manifest" href="site.webmanifest">
    <!-- ============================================================
         META TAGS: Información sobre el documento para el navegador
         ============================================================ -->

    <!-- Indica que el archivo usa caracteres especiales como ñ, á, é -->
    <meta charset="UTF-8">

    <!-- Hace que la página se vea bien en celulares (responsive) -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Título que aparece en la pestaña del navegador -->
    <title>Panel de Administración</title>

    <!-- ============================================================
         ENLACE AL CSS: Le decimos al navegador dónde están los estilos
         ============================================================ -->
    <link rel="stylesheet" href="../estilos.css">

    <!--
        NOTA sobre variables CSS del theme original:
        Las variables de theme.css (--background, --foreground, etc.)
        están integradas directamente en styles.css para no depender
        de PostCSS / TailwindCSS que requieren un proceso de compilación.
    -->
</head>

<body>

    <!-- ============================================================
         LAYOUT PRINCIPAL
         Contenedor que divide la página en: sidebar + contenido
         ============================================================ -->
    <div class="layout">

        <!-- ============================================================
             SIDEBAR (Barra lateral)
             Equivalente al <AdminLayout> de React que envolvía
             el componente y proveía la navegación lateral.
             ============================================================ -->
        <div id="sidebar"></div>
        <!-- FIN SIDEBAR -->


        <!-- ============================================================
             ZONA DE CONTENIDO PRINCIPAL
             ============================================================ -->
        <div class="contenido-principal">

            <!-- Header superior -->
            <header class="header">
                <span>Panel de Administración</span>
            </header>

            <!-- Área donde va el contenido del dashboard -->
            <main class="area-pagina">

                <!-- ================================================
                     ENCABEZADO DEL DASHBOARD
                     Equivalente al bloque <div><h1>...</h1><p>...</p></div>
                     del componente React original
                     ================================================ -->
                <h1 class="dashboard-titulo">
                    Panel de Administración
                </h1>

                <p class="dashboard-subtitulo">
                    Gestiona todos los módulos del sistema
                </p>

                <!-- ================================================
                     CONTENEDOR DE LA GRILLA DE TARJETAS
                     ================================================
                     Este div empieza VACÍO.
                     JavaScript (script.js) lo llenará automáticamente
                     con las 6 tarjetas de módulos al cargar la página.

                     Es equivalente a:
                     <div className="grid md:grid-cols-3 gap-6">
                       {modules.map((module) => <Card ... />)}
                     </div>
                     ================================================ -->
                <div id="grilla-modulos" class="grilla-modulos">
                    <!--
                        👆 ESTE DIV ESTÁ VACÍO EN EL HTML.
                        JavaScript insertará las tarjetas aquí usando:
                        document.getElementById('grilla-modulos').innerHTML = ...
                    -->
                </div>

            </main>
            <!-- FIN área de página -->

        </div>
        <!-- FIN zona de contenido principal -->

    </div>
    <!-- FIN layout principal -->


    <!-- ============================================================
         CARGA DE JAVASCRIPT
         Se pone AL FINAL del body (antes de </body>) para que
         cuando el script se ejecute, todos los elementos HTML
         ya existan. Si lo pusiéramos arriba (en el <head>),
         el script no encontraría el div#grilla-modulos porque
         aún no habría sido "leído" por el navegador.
         ============================================================ -->
    <script src="script.js"></script>
    <script src="sidebar.js"></script>

</body>

</html>