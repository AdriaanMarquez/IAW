# Fase 3 - Diagnóstico Provocado y Defensa

En esta tercera fase he provocado dos errores de configuración en mi infraestructura (uno en Apache y otro en Nginx) para documentar el proceso de diagnóstico y solución, tal y como pide la práctica.

## Fallo 1: Apache (El síndrome del ServerName perdido)

El primer fallo consiste en eliminar la identidad de uno de los Virtual Hosts, provocando que Apache no sepa qué web servir cuando recibe una petición.

*   **Síntoma observado:** Al hacer `curl -k -H "Host: docs.adrian.iaw" https://localhost`, en lugar de salir la web de mi marca pública, el servidor devuelve la página de error por defecto (el comportamiento Catchall de la Fase 1: "Error: Dominio desconocido Adrian").
*   **Hipótesis inicial:** Nginx no está reenviando la cabecera `Host` correctamente, o el Virtual Host de la marca pública no reconoce el dominio de la petición y la deriva al archivo de configuración que actúa por defecto (`000-catchall.conf`).
*   **Comando de diagnóstico usado:** `docker exec -it apache-adrian apachectl -S`. Este comando imprime en pantalla la lista de todos los Virtual Hosts cargados en memoria. En la salida se observaba que debajo del archivo `docs.adrian.conf` no aparecía ningún `namevhost`, confirmando que no tenía nombre asignado.
*   **Causa real y solución:** La directiva `ServerName` estaba comentada (`# ServerName docs.adrian.iaw`) en el archivo de configuración. Al no tener un nombre explícito, Apache no podía emparejar la cabecera del dominio entrante con ese Virtual Host. La solución fue descomentar la directiva en Nano, guardar el archivo y ejecutar `service apache2 reload` dentro del contenedor para aplicar los cambios.

---

## Fallo 2: Nginx (La zona de Rate Limiting huérfana)

El segundo fallo consiste en provocar que el proxy inverso de Nginx se rompa por completo debido a una configuración de seguridad incompleta.

*   **Síntoma observado:** Al lanzar una ráfaga de peticiones (simulando un ataque DDoS con un bucle de `curl`), la terminal empezó a devolver códigos `000` continuos. Esto significa "Connection Refused": la web había quedado totalmente inaccesible y Nginx no respondía en el puerto 443.
*   **Hipótesis inicial:** El servidor Nginx se ha apagado de forma inesperada o ha fallado al intentar reiniciar tras aplicar una nueva configuración, cerrando los puertos de conexión.
*   **Comando de diagnóstico usado:** `docker logs nginx-adrian`. Este comando lee los registros de error del arranque del contenedor. La salida fue clara: `nginx: [emerg] unknown limit_req_zone "limite_adrian"`, indicando un error fatal (emerg) de configuración.
*   **Causa real y solución:** Se intentó aplicar un límite de peticiones usando la directiva `limit_req` dentro del bloque `location`, pero se había borrado la definición de esa zona de memoria (`limit_req_zone`) en el bloque principal del archivo `default.conf`. Al reiniciar, Nginx detectó que le faltaba la directiva principal y abortó el arranque por configuración inválida (haciendo crashear el contenedor). La solución fue volver a escribir la línea de definición de la memoria al principio del archivo y encender el contenedor de nuevo con `docker start nginx-adrian`.

### Tabla de Diagnóstico Provocado

| Síntoma observado | Hipótesis inicial | Comando de diagnóstico usado | Causa real y solución |
| :--- | :--- | :--- | :--- |
| **(Apache)** Al hacer curl al dominio público (`docs.adrian.iaw`), el servidor no muestra mi web, sino que devuelve la página de error por defecto configurada en el Catchall ("Error: Dominio desconocido Adrian"). | Nginx no está pasando bien la cabecera `Host`, o el Virtual Host de Apache no reconoce el dominio entrante y está derivando la petición al archivo por defecto (`000-catchall.conf`). | `docker exec -it apache-adrian apachectl -S` | **Causa:** La directiva `ServerName` estaba comentada en el archivo de configuración, dejando al Virtual Host sin identidad. <br>**Solución:** Descomentar la línea `ServerName docs.adrian.iaw` en el archivo, guardar y ejecutar `service apache2 reload` para aplicar los cambios. |
| **(Nginx)** Al lanzar una ráfaga de peticiones simulando un ataque DDoS, `curl` devuelve un código de error `000` (Connection Refused). El puerto 443 no responde y la web está caída. | El contenedor de Nginx ha crasheado (se ha apagado de forma inesperada) o ha fallado al intentar arrancar tras leer una nueva configuración inválida. | `docker logs nginx-adrian` <br>*(Mostró el error: `unknown limit_req_zone`)* | **Causa:** Se aplicó un límite (`limit_req`) en un bloque `location`, pero se borró la definición de memoria (`limit_req_zone`) en el archivo principal. Nginx abortó su arranque por configuración inválida. <br>**Solución:** Volver a añadir la directiva `limit_req_zone` al principio de `default.conf` y encender el contenedor con `docker start nginx-adrian`. |
