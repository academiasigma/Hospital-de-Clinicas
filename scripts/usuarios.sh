#!/bin/bash

crearUsuario() {
    echo ""
    echo "--- ALTA DE USUARIO ---"
    read -p "Ingrese nombre de usuario (ej: f_guedikian): " nombreUsuario
    if id "$nombreUsuario" &>/dev/null; then
        echo "El usuario ya existe."
    else
        read -p "Ingrese nombre completo / cargo: " nombreCompleto
        read -p "Ingrese grupo primario (dejar vacío para grupo por defecto): " grupoPrimario
        
        if [ -n "$grupoPrimario" ]; then
            if getent group "$grupoPrimario" &>/dev/null; then
                useradd -m -c "$nombreCompleto" -g "$grupoPrimario" -s /bin/bash "$nombreUsuario"
            else
                echo "El grupo no existe. Se creará con su grupo personal."
                useradd -m -c "$nombreCompleto" -s /bin/bash "$nombreUsuario"
            fi
        else
            useradd -m -c "$nombreCompleto" -s /bin/bash "$nombreUsuario"
        fi
        
        echo "Asigne la contraseña para el usuario:"
        passwd "$nombreUsuario"
        echo "Usuario $nombreUsuario creado correctamente con su carpeta /home/$nombreUsuario."
    fi
    read -p "Presione ENTER para continuar."
}

borrarUsuario() {
    echo ""
    echo "--- BAJA DE USUARIO ---"
    read -p "Ingrese el nombre del usuario a borrar: " nombreUsuario
    if id "$nombreUsuario" &>/dev/null; then
        read -p "¿Desea borrar también su carpeta personal /home/$nombreUsuario? (s/n): " borrarHome
        if [ "$borrarHome" = "s" ] || [ "$borrarHome" = "S" ]; then
            userdel -r "$nombreUsuario"
            echo "Usuario y carpeta personal eliminados."
        else
            userdel "$nombreUsuario"
            echo "Usuario eliminado (se conservó su carpeta personal)."
        fi
    else
        echo "El usuario no existe."
    fi
    read -p "Presione ENTER para continuar."
}

modificarUsuario() {
    echo ""
    echo "--- MODIFICAR USUARIO ---"
    read -p "Ingrese el usuario a modificar: " nombreUsuario
    if id "$nombreUsuario" &>/dev/null; then
        echo "1) Modificar Nombre Completo (comentario)"
        echo "2) Modificar y mover carpeta HOME"
        echo "3) Bloquear cuenta de usuario"
        echo "4) Desbloquear cuenta de usuario"
        echo "0) Cancelar"
        read -p "Seleccione una opción: " opcMod
        case $opcMod in
            1)
                read -p "Ingrese el nuevo nombre completo: " nuevoNombre
                usermod -c "$nuevoNombre" "$nombreUsuario"
                echo "Nombre modificado correctamente."
                ;;
            2)
                read -p "Ingrese la nueva ruta del HOME (ej: /home/nuevo_home): " nuevoHome
                usermod -m -d "$nuevoHome" "$nombreUsuario"
                echo "Carpeta HOME modificada."
                ;;
            3)
                usermod -L "$nombreUsuario"
                echo "Cuenta bloqueada."
                ;;
            4)
                usermod -U "$nombreUsuario"
                echo "Cuenta desbloqueada."
                ;;
        esac
    else
        echo "El usuario no existe."
    fi
    read -p "Presione ENTER para continuar."
}

crearGrupo() {
    echo ""
    echo "--- CREAR GRUPO ---"
    read -p "Ingrese el nombre del grupo a crear: " nombreGrupo
    if getent group "$nombreGrupo" &>/dev/null; then
        echo "El grupo ya existe."
    else
        groupadd "$nombreGrupo"
        echo "Grupo $nombreGrupo creado correctamente."
    fi
    read -p "Presione ENTER para continuar."
}

asignarGrupo() {
    echo ""
    echo "--- ASIGNAR USUARIO A GRUPO ---"
    read -p "Ingrese el nombre del usuario: " nombreUsuario
    read -p "Ingrese el grupo secundario: " nombreGrupo
    if id "$nombreUsuario" &>/dev/null && getent group "$nombreGrupo" &>/dev/null; then
        usermod -aG "$nombreGrupo" "$nombreUsuario"
        echo "Usuario agregado al grupo $nombreGrupo."
    else
        echo "Usuario o grupo inexistente."
    fi
    read -p "Presione ENTER para continuar."
}

menuUsuarios() {
    opcUser=99
    while [ "$opcUser" -ne 0 ]; do
        clear
        echo "============================================="
        echo "       S.I.G.S.M. - GESTIÓN DE USUARIOS      "
        echo "============================================="
        echo "1) Crear usuario (con carpeta HOME)"
        echo "2) Borrar usuario"
        echo "3) Modificar usuario / Bloqueo"
        echo "4) Crear grupo"
        echo "5) Asignar usuario a grupo secundario"
        echo "0) Volver al menú principal"
        echo "============================================="
        read -p "Seleccione una opción: " opcUser
        case $opcUser in
            1) crearUsuario ;;
            2) borrarUsuario ;;
            3) modificarUsuario ;;
            4) crearGrupo ;;
            5) asignarGrupo ;;
            0) echo "Volviendo..." ;;
            *) echo "Opción no válida." ;;
        esac
    done
}
