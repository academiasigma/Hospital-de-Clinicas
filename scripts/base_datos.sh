#!/bin/bash

menuBaseDatos() {
    opcDb=99
    while [ "$opcDb" -ne 0 ]; do
        clear
        echo "============================================="
        echo "      S.I.G.S.M. - MOTOR DE BASE DE DATOS    "
        echo "============================================="
        echo "1) Ver estado de MariaDB (systemctl status)"
        echo "2) Iniciar servicio MariaDB"
        echo "3) Detener servicio MariaDB"
        echo "4) Reiniciar servicio MariaDB"
        echo "5) Abrir consola administrativa (MySQL CLI)"
        echo "0) Volver al menú principal"
        echo "============================================="
        read -p "Seleccione una opción: " opcDb
        case $opcDb in
            1)
                systemctl status mariadb --no-pager
                read -p "Presione ENTER para continuar."
                ;;
            2)
                systemctl start mariadb
                echo "Servicio MariaDB iniciado."
                read -p "Presione ENTER para continuar."
                ;;
            3)
                systemctl stop mariadb
                echo "Servicio MariaDB detenido."
                read -p "Presione ENTER para continuar."
                ;;
            4)
                systemctl restart mariadb
                echo "Servicio MariaDB reiniciado."
                read -p "Presione ENTER para continuar."
                ;;
            5)
                echo "Accediendo a la consola de MariaDB..."
                mysql -u root -p
                ;;
            0) echo "Volviendo..." ;;
            *) echo "Opción no válida." ;;
        esac
    done
}
