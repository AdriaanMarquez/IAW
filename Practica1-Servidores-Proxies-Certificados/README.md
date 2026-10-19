# Adrian Marquez Rodriguez - Práctica 1 - Servidores, Proxies y Certificados

Este repositorio contiene la infraestructura completa para NimbusDocs, desplegada con Docker.

## 1. Diseño y Decisiones de Arquitectura

### Nombres de Dominio Elegidos

Para esta práctica he configurado dos marcas bajo la infraestructura de NimbusDocs:

*   **`docs.adrian.iaw` (Marca Pública):** He escogido este nombre para tenerlo fácilmente identificado. Se compone de "docs" para señalar claramente que es el portal de documentación pública, mi nombre "adrian" para personalizar la marca, y la extensión ".iaw" para asegurarme de que pertenece al contexto de la asignatura (Implantación de Aplicaciones Web).
*   **`interno.adrian.iaw` (Marca Privada):** Sigue la misma estructura lógica, pero usando la palabra "interno" para diferenciar a simple vista que este es el Virtual Host restringido destinado a los empleados, el cual contiene protecciones adicionales como la restricción de listados de directorio y la denegación de acceso a archivos sensibles.

### Elección del MPM de Apache (Multi-Processing Module)

Para el backend de Apache he decidido utilizar el módulo **MPM Event** (desactivando `mpm_prefork` y `mpm_worker`), basándome en las siguientes justificaciones técnicas:

1.  **Naturaleza del contenido:** Nuestro servidor NimbusDocs sirve principalmente documentación (archivos HTML estáticos). El MPM Event está diseñado específicamente para brillar en este escenario de contenido estático y alta concurrencia.
2.  **Gestión de conexiones (Keep-Alive):** A diferencia de `prefork` (que dedica un proceso entero y pesado a cada usuario), `event` es multihilo y delega el mantenimiento de las conexiones inactivas (Keep-Alive) a un hilo secundario dedicado. Esto libera a los hilos de trabajo inmediatamente después de enviar un archivo, permitiendo atender a muchísimos más usuarios con un consumo de memoria RAM drásticamente menor.
3.  **Arquitectura con Proxy Inverso:** Al tener a Nginx actuando como escudo por delante, Apache ya no tiene que lidiar con conexiones lentas de internet. Nginx le entrega las peticiones ya masticadas, por lo que el modelo multihilo de MPM Event es la opción más rápida para procesar esas ráfagas de peticiones locales que llegan por el puerto 80.


## Cómo arrancar el proyecto

1. Añadir los dominios al fichero hosts:
`sudo nano /etc/hosts`
```
   127.0.0.1  docs.adrian.iaw  interno.adrian.iaw
```

2. Levantar la infraestructura (los sitios de Apache ya se activan solos desde su Dockerfile):

```bash
   docker compose up -d --build
   docker compose ps
```

3. Probar (el navegador avisará del certificado autofirmado; es normal):

```bash
   curl -k -H "Host: docs.adrian.iaw" https://localhost
   curl -k -H "Host: interno.adrian.iaw" https://localhost
   curl -k -H "Host: docs.adrian.iaw" https://localhost/config-db-nimbus.ini
```
