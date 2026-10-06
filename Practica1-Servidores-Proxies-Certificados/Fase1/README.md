I. Diseño y Requisitos

Antes de tocar una terminal, documentad en el README estas decisiones — la observación directa empezará precisamente por aquí:
    • Los dos dominios ficticios elegidos (uno por marca) y por qué los habéis nombrado así.
        
        docs.adrian.iaw
        interno.adrian.iaw

        He escogido estos nombres para tenerlos mas identificados a la hora de buscarlos, se compone de docs para la marca pública y interno para el interno, mi nombre Adrián y .iaw para asegurarme de la asignatura 

    • El MPM de Apache elegido y la justificación técnica de esa elección (repasad UD2, bloque de MPM).

        Después de mirar las dos opciones veo mas coherente usar "even". 
        Mi servidor es más de contenido estático y multihilo 


FASE 1 Estructura de Carpetas

Practica1-Servidores-Proxies-Certificados/
├── docker-compose.yml
├── Fase1/
│   └── README.md
├── apache-config/
│   └── sites-available/
│       ├── 000-catchall.conf
│       ├── docs.adrian.conf
│       └── interno.adrian.conf
└── www/
    ├── catchall/
    │   └── index.html
    ├── docs/
    │   └── index.html
    └── interno/
        ├── index.html
        └── config-db-nimbus.ini


Aqui muestro toda la configuracion del docker compose 

services:
  apache-nimbus:
    image: ubuntu/apache2:latest
    container_name: apache-adrian
    ports:
      - "8080:80"
    volumes:
      - ./apache-config/sites-available:/etc/apache2/sites-available
      - ./www:/var/www

Crea estos tres archivos dentro de la carpeta apache-config/sites-available/:

000-catchall.conf
Apache

<VirtualHost *:80>
    ServerName default.local
    DocumentRoot /var/www/catchall
    
    <Directory /var/www/catchall>
        Require all granted
    </Directory>
</VirtualHost>

docs.adrian.conf
Apache

<VirtualHost *:80>
    ServerName docs.adrian.iaw
    DocumentRoot /var/www/docs

    ErrorLog ${APACHE_LOG_DIR}/docs_error.log
    CustomLog ${APACHE_LOG_DIR}/docs_access.log combined

    <Directory /var/www/docs>
        Require all granted
    </Directory>
</VirtualHost>

interno.adrian.conf
Apache

<VirtualHost *:80>
    ServerName interno.adrian.iaw
    DocumentRoot /var/www/interno

    ErrorLog ${APACHE_LOG_DIR}/interno_error.log
    CustomLog ${APACHE_LOG_DIR}/interno_access.log combined

    <Directory /var/www/interno>
        Options -Indexes
        Require all granted
    </Directory>

    <Files "config-db-nimbus.ini">
        Require all denied
    </Files>
</VirtualHost>

2. Archivos Web (HTML)

Crea estos archivos dentro de sus respectivas carpetas en www/:

www/catchall/index.html
HTML

<h1>Error: Dominio desconocido Adrian</h1>

www/docs/index.html
HTML

<h1>Esta es la marca Publica de Adrian</h1>

www/interno/index.html
HTML

<h1>Esta es la marca interna la cual es privada y ademas esta protegida</h1>

www/interno/config-db-nimbus.ini
Ini, TOML

user=admin
password=secreto

3. Comandos para la terminal

Pega estos comandos en tu terminal (en la carpeta del proyecto) para levantar todo y entrar al contenedor:
Bash

docker compose up -d
docker exec -it apache-adrian bash

Pega estos comandos una vez estés dentro del contenedor para activar las webs:
Bash

a2dissite 000-default.conf
a2ensite 000-catchall.conf docs.adrian.conf interno.adrian.conf
a2dismod mpm_prefork mpm_worker
a2enmod mpm_event
service apache2 reload
exit

Pega estos comandos en tu terminal normal para probar que funciona y hacer las capturas:
Bash

curl -H "Host: docs.adrian.iaw" http://localhost:8080
curl -H "Host: interno.adrian.iaw" http://localhost:8080
curl -H "Host: interno.adrian.iaw" http://localhost:8080/config-db-nimbus.ini
curl -H "Host: minombre.es" http://localhost:8080


## 2. Estructura de carpetas para dockers

Para usar Apache sobre dockers y llevármelo de casa a clase sin instalar nada directamente en mi PC, la estructura de archivos que tengo creada es esta:

```text
/apache-config
  /sites-available
    000-catchall.conf
    docs.adrian.conf
    interno.adrian.conf
/www
  /catchall
    index.html
  /docs
    index.html
  /interno
    index.html
    config-db-nimbus.ini  <-- Este es el archivo sensible con nombre propio
docker-compose.yml

En los archivos .conf, el DocumentRoot apunta a la ruta /var/www/carpeta que está enlazada (mediante volúmenes) con mis carpetas locales de www. En el archivo interno.adrian.conf también le he metido el Options -Indexes para desactivar el listado de directorios y el Require all denied para proteger el archivo .ini.
3. Pasos para levantarlo todo

1. Arrancar el contenedor:
Desde la terminal, situado donde está el docker-compose.yml, arranco los dockers en segundo plano:
Bash

docker compose up -d

2. Entrar al contenedor:
Tengo que meterme dentro del contenedor (al que le puse el nombre de apache-adrian) para activar las configuraciones:
Bash

docker exec -it apache-adrian bash

3. Activar las cosas desde DENTRO:
Una vez dentro (el prompt cambia), ejecuto estos comandos para apagar el sitio que viene por defecto, encender los míos, poner el MPM y recargar:
Bash

a2dissite 000-default.conf
a2ensite 000-catchall.conf docs.adrian.conf interno.adrian.conf
a2dismod mpm_prefork mpm_worker
a2enmod mpm_event
service apache2 reload
exit
