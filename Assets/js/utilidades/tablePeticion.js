export function configurarTabla(selector, url, columnas, botones = [], searchElements = {}) {
    const { searchInputId, searchBtnId, clearSearchBtnId } = searchElements; // IDs de los elementos de búsqueda

    const tabla = $(selector);
    const contenedor = tabla.closest('.table-responsive');
    const searchInput = $(`#${searchInputId}`); // Referencia del campo de búsqueda
    contenedor.removeClass('visible');

    const dt = tabla.DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        pageLength: 5,
        lengthMenu: [5, 10, 15, 20],
        ordering: false,
        ajax: {
            url: base_url + url,
            type: "POST",
            dataType: "json",
            data: function (d) {
                // Añadir el valor de búsqueda personalizada
                d.searchValue = searchInput.val();
                return d;
            },
        },
        language: {
            "url": "Assets/js/utilidades/es-ES.json"
        },
        dom: "<'row'<'col-sm-12'l>>" +
            "<'row'<'col-sm-6'B><'col-sm-6'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: botones,
        columns: columnas,
        initComplete: function () {
            contenedor.addClass('visible');
        }
    });

    // Búsqueda personalizada solo al presionar Enter
    searchInput.on('keypress', function (e) {
        if (e.which == 13) { // Enter
            dt.search(searchInput.val()).draw();
        }
    });

    // Botón de búsqueda
    $(`#${searchBtnId}`).on('click', function () {
        dt.search(searchInput.val()).draw();
    });

    // Botón de borrar búsqueda
    $(`#${clearSearchBtnId}`).on('click', function () {
        searchInput.val(''); // Limpia el campo de búsqueda
        dt.search('').draw();
    });

    return dt;
}

export async function enviarPeticion(url, metodo, datos, callback) {
    try {
        let response;
        if (metodo === "POST") {
            response = await fetch(url, {
                method: 'POST',
                body: new FormData(datos)
            });
        } else {
            response = await fetch(url, { method: 'GET' });
        }

        if (response.ok) {
            const data = await response.json();
            callback(data);
        } else {
            console.error('Error en la petición:', response.statusText);
        }
    } catch (error) {
        console.error('Error al enviar la petición:', error);
        Swal.fire({
            icon: 'error',
            title: 'Hubo un problema al realizar la acción',
            text: 'Por favor, inténtalo nuevamente más tarde.'
        });
    }
}

export function formatearMoneda(input) {
    let valor = input.value;

    // Eliminar caracteres no numéricos excepto comas y puntos
    valor = valor.replace(/[^0-9.,]/g, '');

    // Asegurar que solo haya un punto decimal
    let partes = valor.split('.');
    if (partes.length > 2) {
        valor = partes[0] + '.' + partes.slice(1).join('');
    }

    // Evitar múltiples comas consecutivas
    valor = valor.replace(/,{2,}/g, ',');

    // Evitar que el número comience con una coma o punto
    valor = valor.replace(/^[,.]/, '');

    // Actualizar el valor del input
    input.value = valor;
}

export function formatearNumero(numero) {
    return parseFloat(numero).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// evento para activar botones
export function asignarEvento(id, evento, handler) {
    const elemento = document.getElementById(id);
    if (elemento) {
        elemento.addEventListener(evento, handler);
    } else {
        console.warn(`Elemento con id "${id}" no encontrado.`);
    }
}




