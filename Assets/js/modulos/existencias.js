

$(document).ready(function () {
    $("#producto").select2({
        placeholder: "Buscar por nombre",
        allowClear: true,
        ajax: {
            url: base_url + "Existencias/buscarProducto", // Ruta de búsqueda en tu backend
            dataType: "json",
            delay: 250, // Espera para reducir solicitudes
            data: function (params) {
                return {
                    q: params.term // El término de búsqueda
                };
            },
            processResults: function (data) {
                //console.log("Datos recibidos:", data);
                // Mapea los resultados para adaptarlos al formato de Select2
                return {
                    results: data.map(producto => ({
                        id: producto.codigo,
                        text: producto.nombre
                    }))
                };
            },
            cache: true
        }
    });
    // para selecionar del select
    $("#producto").on("select2:select", function (e) {
        let data = e.params.data;
        //console.log(data.id);
        producto(data.id);
    });
    //para cancelar la seleccion
    $("#producto").on("select2:unselect", function (e) {
        var table = document.getElementById('stockProductos');

        table.innerHTML = '';
    });
});

function producto(codigoProducto) {
    const url = base_url + "Existencias/existencias/" + codigoProducto;
    fetch(url)
        .then(response => response.json())
        .then(res => {
            //console.log(res);
            if (res.error) {
                document.getElementById("stockProductos").innerHTML = `<tr><td colspan="2">${res.error}</td></tr>`;
            } else if (Array.isArray(res) && res.length > 0) {
                let html = '';
                res.forEach(row => {
                    html += `<tr>
                                <td>${row.codigoProyecto}</td>
                                <td>${row.cantidadProducto}</td>
                            </tr>`;
                });
                document.getElementById("stockProductos").innerHTML = html;
            } else {
                document.getElementById("stockProductos").innerHTML = `<tr><td colspan="2">No hay existencias</td></tr>`;
            }
        })
        .catch(error => {
            console.error("Error al obtener existencias:", error);
            document.getElementById("stockProductos").innerHTML = `<tr><td colspan="2">Error al obtener existencias</td></tr>`;
        });
}

