<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Administración de Convenios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <link rel="icon" type="image/svg+xml" href="../favicon-32x32.png" />
    
</head>

<body class="p-4 " >    
    <div class="container">
    <div id="loader" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
 z-index: 1050;">
  <div class="text-center">
    <img src="https://i.gifer.com/ZZ5H.gif" alt="Cargando..." width="80">
    <p class="mt-2">Cargando...</p>
  </div>
</div>
    <img src="../image-removebg-preview.png" alt="" class="mb-3">
    <div class="d-flex justify-content-end mb-3">
    <button class="btn-rechazar" onclick="descargarCSV()" style="width:200px"> Descargar Aprobados CSV</button>
    </div>
        <h1 class="text-center mb-4">Convenios PullmanBus</h1>
        <div class="mb-3"><a href="php/logout.php" class="btn btn-rechazar">
        <i class="bi bi-box-arrow align-items-center"></i> Salir
      </a></div>
     
        <div id="visorContainer" style="display: none; margin-top: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h5 style="margin: 0;">Visor de Documento</h5>
                <button onclick="cerrarVisor()" class="btn btn-sm btn-outline-danger">Cerrar</button>
            </div>
            <div style="display: flex; justify-content: center; margin-top: 10px;">
                <iframe id="visorDocumento" width="90%" height="500px" style="border: 1px solid #ccc;"></iframe>
            </div>
        </div>


        <table class="table table-bordered table-hover" id="tablaEstudiantes">
            <thead class="table-title">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>RUT</th>
                    <th>Email</th>
                    <th>Origen - Destino</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Documentos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
          </div>
  

    <!-- JS & DataTables -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        function descargarCSV() {
    fetch('./php/obtener_postulaciones.php')
        .then(res => res.json())
        .then(data => {
            const aprobados = data.filter(est => est.estado === 'Aprobada');

            if (aprobados.length === 0) {
                alert('No hay registros aprobados para descargar.');
                return;
            }

            let csvContent = 'Nombre,RUT\n'; // Cabecera del CSV
            aprobados.forEach(est => {
                const nombreCompleto = `${est.nombres} ${est.apellidos}`;
                csvContent += `"${nombreCompleto}","${est.rut}"\n`;
            });

            // Crear un blob y descargar
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);

            const a = document.createElement('a');
            a.href = url;
            a.download = 'postulaciones_aprobadas.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        })
        .catch(error => {
            console.error('Error al generar CSV:', error);
            alert('Hubo un error al generar el archivo.');
        });
}
        
        function verDocumento(url) {
            const visor = document.getElementById('visorDocumento');
            const contenedor = document.getElementById('visorContainer');

            visor.src = url;
            contenedor.style.display = 'block';
            contenedor.scrollIntoView({ behavior: 'smooth' });
        }

        function cerrarVisor() {
            const visor = document.getElementById('visorDocumento');
            const contenedor = document.getElementById('visorContainer');
            visor.src = '';
            contenedor.style.display = 'none';
        }


        function verDocumento(url) {
            const visor = document.getElementById('visorDocumento');
            const contenedor = document.getElementById('visorContainer');

            // Usa visor.html como plantilla para centrar
            visor.src = `visor.html?src=${encodeURIComponent(url)}`;
            contenedor.style.display = 'block';
            contenedor.scrollIntoView({ behavior: 'smooth' });
        }

        function actualizarEstado(id, estado) {
            const loader = document.getElementById('loader');
            loader.style.display = 'block';

            fetch('php/actualizar_estado.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}&estado=${estado}`
            })
            .then(() => cargarPostulaciones())
            .catch(err => {
                console.error("Error al actualizar estado:", err);
                alert("Hubo un error al actualizar el estado.");
            })
            .finally(() => {
                loader.style.display = 'none';
            });
        }


        function cargarPostulaciones() {
            fetch('./php/obtener_postulaciones.php')
                .then(response => response.json())
                .then(data => {
                    const tabla = $('#tablaEstudiantes').DataTable();
                    tabla.clear().draw();

                    data.forEach(est => {
                        // const estadoColor = est.estado === 'Aprobada' ? 'success' :
                        //    est.estado === 'Rechazada' ? 'danger' : 'secondary';
                        const estadoColor = est.estado === 'Rechazada' ? 'secondary' : 'primary';

                        tabla.row.add([
                            est.id,
                            `${est.nombres} ${est.apellidos}`,
                            est.rut,
                            est.email,
                            `${est.origen} - ${est.destino}`,
                            `<span class="badge-${estadoColor} ">${est.estado}</span>`,
                            est.fecha_postulacion,
                            `
              <button onclick="verDocumento('../php/${est.cedula_path}')" class="btn btn-sm btn-outline-primary btn-documento">Cédula</button>
              <button onclick="verDocumento('../php/${est.certificado_path}')" class="btn btn-sm btn-outline-primary btn-documento">Certificado</button>
              `,
                            `
              <button onclick="actualizarEstado(${est.id}, 'Aprobada')" class="btn btn-sm btn-success btn-accion btn-aprobar">Aprobar</button>
              <button onclick="actualizarEstado(${est.id}, 'Rechazada')" class="btn btn-sm btn-danger btn-accion btn-rechazar">Rechazar</button>
              `
                        ]).draw();
                    });
                });
        }

        $(document).ready(function () {
            $('#tablaEstudiantes').DataTable();
            cargarPostulaciones();
        });
    </script>
   
</body>

</html>