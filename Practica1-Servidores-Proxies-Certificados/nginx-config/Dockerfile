# Partimos de la imagen oficial de Nginx
FROM nginx:latest

# Copiamos nuestra configuración del proxy y rate limiting
COPY default.conf /etc/nginx/conf.d/default.conf

# Copiamos la carpeta entera con los certificados TLS
COPY ssl/ /etc/nginx/ssl/
