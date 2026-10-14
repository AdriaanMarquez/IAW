# Partimos de la imagen base oficial de Ubuntu con Apache
FROM ubuntu/apache2:latest

# Copiamos nuestros Virtual Hosts al interior de la imagen
COPY sites-available/ /etc/apache2/sites-available/

# Desactivamos lo que no queremos (sitio por defecto y MPMs antiguos)
RUN a2dissite 000-default.conf && \
    a2dismod mpm_prefork mpm_worker

# Activamos el MPM Event de alto rendimiento y nuestros sitios
RUN a2enmod mpm_event && \
    a2ensite 000-catchall.conf docs.adrian.conf interno.adrian.conf

# El puerto interno por el que escucha Apache
EXPOSE 80
