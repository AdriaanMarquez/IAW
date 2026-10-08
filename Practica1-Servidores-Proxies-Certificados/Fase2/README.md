1. Estructura de Carpetas a Crear/Verificar

Asegúrate de que tu proyecto tiene esta estructura (solo necesitas crear la carpeta nginx-config y sus subcarpetas):
Plaintext

Practica1-Servidores-Proxies-Certificados/
├── docker-compose.yml
├── Fase1/
│   └── README.md
├── Fase2/
│   └── README.md
├── apache-config/
│   └── sites-available/
│       ├── 000-catchall.conf
│       ├── docs.adrian.conf
│       └── interno.adrian.conf
├── www/
│   └── (Tus carpetas HTML de la fase 1)
└── nginx-config/
    ├── default.conf
    └── ssl/

2. Archivos de Configuración

Reemplaza el contenido de tu docker-compose.yml en la raíz del proyecto por este:

docker-compose.yml
YAML

services:
  apache-nimbus:
    image: ubuntu/apache2:latest
    container_name: apache-adrian
    volumes:
      - ./apache-config/sites-available:/etc/apache2/sites-available
      - ./www:/var/www

  nginx-nimbus:
    image: nginx:latest
    container_name: nginx-adrian
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./nginx-config/default.conf:/etc/nginx/conf.d/default.conf
      - ./nginx-config/ssl:/etc/nginx/ssl
    depends_on:
      - apache-nimbus

Crea este archivo dentro de la nueva carpeta nginx-config/:

nginx-config/default.conf
Nginx

# 1. Definición del Rate Limiting (Memoria: 10MB, Límite: 5 peticiones/segundo)
limit_req_zone $binary_remote_addr zone=limite_adrian:10m rate=5r/s;

# 2. Redirección HTTP a HTTPS
server {
    listen 80;
    server_name docs.adrian.iaw interno.adrian.iaw;
    return 301 https://$host$request_uri;
}

# 3. Servidor HTTPS seguro y Proxy Inverso
server {
    listen 443 ssl;
    server_name docs.adrian.iaw interno.adrian.iaw;

    ssl_certificate /etc/nginx/ssl/adrian.crt;
    ssl_certificate_key /etc/nginx/ssl/adrian.key;
    
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5; 

    # Cabeceras de Seguridad
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "SAMEORIGIN" always;

    location / {
        # Aplicamos el Rate Limiting (permite ráfagas de hasta 10 sin retraso)
        limit_req zone=limite_adrian burst=10 nodelay;

        proxy_pass http://apache-nimbus:80;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}

3. Comandos para la terminal

Pega este bloque en tu terminal (en la raíz del proyecto) para crear el certificado de seguridad y levantar los contenedores:
Bash

mkdir -p nginx-config/ssl
openssl ecparam -genkey -name prime256v1 -out nginx-config/ssl/adrian.key
openssl req -new -x509 -days 365 -key nginx-config/ssl/adrian.key -out nginx-config/ssl/adrian.crt -subj "/CN=docs.adrian.iaw"
docker compose down
docker compose up -d
docker exec -it apache-adrian bash

Pega estos comandos una vez estés dentro del contenedor de Apache (para volver a activar las webs, ya que el contenedor se recreó):
Bash

a2dissite 000-default.conf
a2ensite 000-catchall.conf docs.adrian.conf interno.adrian.conf
a2dismod mpm_prefork mpm_worker
a2enmod mpm_event
service apache2 reload
exit

Pega estos comandos en tu terminal normal para hacer las pruebas y capturas finales:
Bash

# Prueba de que la web carga por HTTPS
curl -k -H "Host: docs.adrian.iaw" https://localhost

# Prueba de las cabeceras de seguridad inyectadas por Nginx
curl -k -I -H "Host: docs.adrian.iaw" https://localhost

# Prueba del ataque DDoS para comprobar el Rate Limiting (Códigos 503)
for i in {1..20}; do curl -k -s -o /dev/null -w "%{http_code}\n" -H "Host: docs.adrian.iaw" https://localhost; done

4. Entregable (El README de la Fase 2)

Copia todo este texto y pégalo tal cual en tu archivo Fase2/README.md:
Markdown

# Fase 2 - Proxy Inverso y Seguridad con Nginx

En esta segunda fase he puesto un servidor Nginx por delante de mi Apache. Ahora Apache está "escondido" (ya no expone sus puertos hacia fuera) y Nginx es el que da la cara, encargándose de la seguridad y de pasarle el tráfico limpio a Apache.

## 1. Certificados y Cifrado (TLS)
Lo primero que hice fue crear las claves para que la web sea segura (HTTPS). Usé la herramienta de comandos OpenSSL para generar una clave privada usando la curva elíptica ECDSA (`prime256v1`), que es la que pedía la práctica porque es más moderna, rápida y segura que las antiguas RSA.
Con esa clave, generé mi propio certificado (autofirmado) poniéndole como nombre principal (CN) mi marca pública: `docs.adrian.iaw`.
Para rematar la seguridad de la conexión, configuré Nginx para que solo acepte protocolos modernos (`TLSv1.2` y `TLSv1.3`), rechazando cualquier versión antigua que pueda tener vulnerabilidades.

## 2. Redirección HTTP a HTTPS
Para asegurar que nadie navegue en texto plano, creé un bloque en Nginx que escucha por el puerto 80 (HTTP). Si alguien entra por ahí, Nginx le devuelve un código de redirección `301` (Permanente) y lo manda a la misma URL pero con `https://`. He elegido usar un 301 en lugar de un 302 porque es mejor para el rendimiento y el SEO: le dice a los navegadores que guarden en su caché que esta web ahora siempre es segura.

## 3. Configuración del Proxy Inverso
En el bloque del puerto 443 (HTTPS), he configurado la directiva `proxy_pass`. Nginx recibe el tráfico, lo descifra usando el certificado, y se lo manda por HTTP interno a mi contenedor de Apache. 
Aquí la clave técnica ha sido añadir la línea `proxy_set_header Host $host;`. Si no ponía esto, Apache recibía las peticiones sin saber a qué dominio iban dirigidas y me saltaba siempre la página de error del Catchall que configuré en la Fase 1.

## 4. Cabeceras de Seguridad Adicionales
Para proteger no solo el servidor, sino también a los clientes que visitan la web, he hecho que Nginx inyecte tres cabeceras de seguridad en cada respuesta:
*   **HSTS (Strict-Transport-Security):** Obliga al navegador del usuario a usar siempre HTTPS para mi web durante un año entero. Así, aunque escriban "http://", el propio navegador lo cambia antes de enviar la petición.
*   **X-Content-Type-Options (nosniff):** Le prohíbe al navegador intentar adivinar el tipo de los archivos. Esto evita ataques donde un hacker sube código malicioso camuflado con extensión de imagen.
*   **X-Frame-Options (SAMEORIGIN):** Nos protege contra el "Clickjacking", impidiendo que webs de terceros carguen nuestra página dentro de un iframe para robar clics o engañar a los usuarios.

## 5. Rate Limiting (Protección anti-Spam y DDoS)
Por último, para evitar que tumben el servidor backend a base de miles de peticiones por segundo, he configurado una zona de memoria (`limit_req_zone`) que registra las IPs. 
Le he puesto un límite muy estricto de 5 peticiones por segundo (`5r/s`). Si un atacante supera ese ritmo, Nginx actúa de escudo, deja de molestar a Apache y le devuelve directamente al atacante un código de error `503 Service Unavailable`, salvando al servidor Apache de saturarse.
