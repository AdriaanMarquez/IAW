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
    ├── docs.adrian/
    │   └── index.html
    └── interno.adrian/
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
    DocumentRoot /var/www/docs.adrian

    ErrorLog ${APACHE_LOG_DIR}/docs_error.log
    CustomLog ${APACHE_LOG_DIR}/docs_access.log combined

    <Directory /var/www/docs.adrian>
        Require all granted
    </Directory>
</VirtualHost>

interno.adrian.conf
Apache

<VirtualHost *:80>
    ServerName interno.adrian.iaw
    DocumentRoot /var/www/interno.adrian

    ErrorLog ${APACHE_LOG_DIR}/interno_error.log
    CustomLog ${APACHE_LOG_DIR}/interno_access.log combined

    <Directory /var/www/interno.adrian>
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

www/docs.adrian/index.html
HTML

<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><title>NimbusDocs - Docs.Adrian</title></head>
<body><h1>Esta es la marca pública de Adrian</h1></body>
</html>

www/interno.adrian/index.html
HTML

<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><title>NimbusDocs - Interno</title></head>
<body><h1>Esta es la marca interna, privada y protegida</h1></body>
</html>

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
