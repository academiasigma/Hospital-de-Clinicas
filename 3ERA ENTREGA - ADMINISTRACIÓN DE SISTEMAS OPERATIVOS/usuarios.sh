#!/bin/bash
# ¡¡Módulo de Gestión de Usuarios y grupos!!
crear_usuario() {
    requiere_root || return 1
    local usuario=""
    local nombre_completo=""
    local grupo_primario=""

    echo "--- ALTA DE USUARIO ---"
    read -r -p "Ingrese nombre de usuario (ej: f_guedikian): " usuario

    if [ -z "$usuario" ]; then
        mensaje_error "El nombre de usuario no puede quedar vacío."
        pausa
        return 1
    fi

    if usuario_existe "$usuario"; then
        mensaje_error "El usuario '$usuario' ya existe en el sistema."
        pausa
        return 1
    fi

    read -r -p "Ingrese nombre completo / cargo: " nombre_completo
    read -r -p "Ingrese grupo primario (dejar vacío para grupo por defecto): " grupo_primario

    if [ -n "$grupo_primario" ]; then
        if ! grupo_existe "$grupo_primario"; then
            mensaje_error "El grupo primario '$grupo_primario' no existe."
            pausa
            return 1
        fi
        useradd -m -s /bin/bash -c "$nombre_completo" -g "$grupo_primario" "$usuario"
    else
        useradd -m -s /bin/bash -c "$nombre_completo" "$usuario"
    fi

    if [ $? -eq 0 ]; then
        mensaje_ok "Usuario '$usuario' creado correctamente."
        echo "Asigne la contraseña para el nuevo usuario:"
        passwd "$usuario"
    else
        mensaje_error "Ocurrió un fallo al intentar crear el usuario."
    fi
    pausa
}

borrar_usuario() {
    requiere_root || return 1
    local usuario=""
    local borrar_home=""

    echo "--- BAJA DE USUARIO ---"
    read -r -p "Ingrese el nombre del usuario a eliminar: " usuario

    if [ -z "$usuario" ]; then
        mensaje_error "Debe especificar un usuario."
        pausa
        return 1
    fi

    if ! usuario_existe "$usuario"; then
        mensaje_error "El usuario '$usuario' no existe."
        pausa
        return 1
    fi

    read -r -p "¿Desea eliminar también su carpeta personal /home/$usuario? (s/n): " borrar_home

    if [ "$borrar_home" = "s" ] || [ "$borrar_home" = "S" ]; then
        userdel -r "$usuario" &>/dev/null
        mensaje_ok "Usuario '$usuario' y su carpeta personal fueron eliminados."
    else
        userdel "$usuario" &>/dev/null
        mensaje_ok "Usuario '$usuario' eliminado (se conservó el directorio home)."
    fi
    pausa
}

modificar_usuario() {
    requiere_root || return 1
    local usuario=""
    local opc_mod=""

    echo "--- MODIFICAR / BLOQUEAR USUARIO ---"
    read -r -p "Ingrese el nombre del usuario: " usuario

    if ! usuario_existe "$usuario"; then
        mensaje_error "El usuario '$usuario' no existe."
        pausa
        return 1
    fi

    echo "1) Cambiar contraseña"
    echo "2) Bloquear cuenta (usermod -L)"
    echo "3) Desbloquear cuenta (usermod -U)"
    read -r -p "Seleccione acción: " opc_mod

    case "$opc_mod" in
        1) passwd "$usuario" ;;
        2)
            usermod -L "$usuario"
            mensaje_ok "Cuenta del usuario '$usuario' bloqueada exitosamente."
            ;;
        3)
            usermod -U "$usuario"
            mensaje_ok "Cuenta del usuario '$usuario' desbloqueada exitosamente."
            ;;
        *)
            mensaje_error "Opción no válida."
            ;;
    esac
    pausa
}

crear_grupo() {
    requiere_root || return 1
    local grupo=""

    echo "--- CREAR GRUPO ---"
    read -r -p "Ingrese el nombre del nuevo grupo: " grupo

    if [ -z "$grupo" ]; then
        mensaje_error "El nombre del grupo no puede quedar vacío."
        pausa
        return 1
    fi

    if grupo_existe "$grupo"; then
        mensaje_error "El grupo '$grupo' ya existe."
        pausa
        return 1
    fi

    groupadd "$grupo"
    if [ $? -eq 0 ]; then
        mensaje_ok "Grupo '$grupo' creado correctamente."
    else
        mensaje_error "No se pudo crear el grupo '$grupo'."
    fi
    pausa
}

gestion_grupo_secundario() {
    requiere_root || return 1
    local usuario=""
    local grupo=""
    local accion=""

    echo "--- GESTIÓN DE GRUPO SECUNDARIO ---"
    read -r -p "¿Desea (A)gregar o (E)liminar del grupo secundario? (a/e): " accion
    read -r -p "Ingrese nombre del usuario: " usuario
    read -r -p "Ingrese nombre del grupo secundario: " grupo

    if [ -z "$usuario" ] || [ -z "$grupo" ]; then
        mensaje_error "Los campos no pueden quedar vacíos."
        pausa
        return 1
    fi

    if ! usuario_existe "$usuario"; then
        mensaje_error "El usuario '$usuario' no existe."
        pausa
        return 1
    fi

    if ! grupo_existe "$grupo"; then
        mensaje_error "El grupo '$grupo' no existe."
        pausa
        return 1
    fi

    case "$accion" in
        a|A)
            gpasswd -a "$usuario" "$grupo" &>/dev/null
            mensaje_ok "Usuario '$usuario' añadido al grupo '$grupo'."
            ;;
        e|E)
            gpasswd -d "$usuario" "$grupo" &>/dev/null
            mensaje_ok "Usuario '$usuario' eliminado del grupo '$grupo'."
            ;;
        *)
            mensaje_error "Acción desconocida. Debe ingresar 'a' o 'e'."
            ;;
    esac
    pausa
}

listar_usuarios() {
    echo "--- LISTADO DE USUARIOS REALES DEL SISTEMA (UID >= 1000) ---"
    echo ""
    local u p uid gid c home sh
    while IFS=: read -r u p uid gid c home sh; do
        if [ "$uid" -ge 1000 ] && [ "$uid" -ne 65534 ]; then
            printf "%-18s | UID: %-5s | Home: %-22s | Shell: %s\n" "$u" "$uid" "$home" "$sh"
        fi
    done < /etc/passwd
    echo ""
    pausa
}

ver_grupos_usuario() {
    local usuario=""
    read -r -p "Ingrese el usuario a consultar: " usuario

    if usuario_existe "$usuario"; then
        groups "$usuario"
    else
        mensaje_error "El usuario '$usuario' no existe."
    fi
    pausa
}

menu_usuarios() {
    local opcion=""
    while [ "$opcion" != "0" ]; do
        titulo "S.I.G.S.M. - GESTIÓN DE USUARIOS Y GRUPOS"
        echo " 1) Crear usuario (con carpeta HOME)"
        echo " 2) Borrar usuario"
        echo " 3) Modificar usuario / Bloqueo de cuenta"
        echo " 4) Crear grupo"
        echo " 5) Asignar / Quitar de grupo secundario"
        echo " 6) Listar usuarios del sistema (UID >= 1000)"
        echo " 7) Ver grupos a los que pertenece un usuario"
        echo " 0) Volver al menú principal"
        echo "==================================================================="
        read -r -p "Seleccione una opción: " opcion

        case "$opcion" in
            1) crear_usuario ;;
            2) borrar_usuario ;;
            3) modificar_usuario ;;
            4) crear_grupo ;;
            5) gestion_grupo_secundario ;;
            6) listar_usuarios ;;
            7) ver_grupos_usuario ;;
            0) break ;;
            *) mensaje_error "Opción no válida"; pausa ;;
        esac
    done
}
