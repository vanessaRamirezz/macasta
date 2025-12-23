function frmLogin(e) {
    e.preventDefault();
    const usuario = document.getElementById("usuario");
    const clave = document.getElementById("clave");

    if (usuario.value == "") {
        clave.classList.remove("is-invalid");
        usuario.classList.add("is-invalid");
        usuario.focus();
    } else if (clave.value == "") {
        usuario.classList.remove("is-invalid");
        clave.classList.add("is-invalid");
        clave.focus();
    } else {
        const url = base_url + "Usuarios/validar";
        const frm = document.getElementById("frmLogin");
        
        // Crear el objeto FormData
        const formData = new FormData(frm);
        
        // Usar fetch para enviar los datos
        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())  // Parsear la respuesta JSON
        .then(data => {
            if (data == "ok") {
                window.location = base_url + "Inicio";
            } else {
                document.getElementById("alerta").classList.remove("d-none");
                document.getElementById("alerta").innerHTML = data;
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
}
