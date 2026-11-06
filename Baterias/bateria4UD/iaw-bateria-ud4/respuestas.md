# Explícalo con tus palabras

Cuando tengas el comprobador en verde, contesta aquí sin mirar los apuntes (2–4 líneas cada una).
Es lo que te preguntarán en la defensa de la práctica.

1. ¿Qué pasa si a Nginx le falta el bloque `location ~ \.php$`? ¿Por qué es peligroso?
Si falta, Nginx no sabe qué hacer con los archivos PHP y se los descarga directamente al usuario como si fueran un documento de texto cualquiera. Es muy peligroso porque cualquier persona podría ver las contraseñas de la base de datos y los datos secretos de la web.

2. ¿Para qué sirve `try_files $uri =404;` dentro del bloque de PHP? ¿Qué hace Nginx con un `.php` que no existe, con él y sin él?
Sirve para comprobar si el archivo PHP realmente existe en el ordenador antes de abrirlo. Con él, si no existe, avisa al momento con un error 404; sin él, intentaría ejecutar un archivo vacío o falso, lo cual puede causar fallos de seguridad.

3. En `pedido.php`, ¿por qué el XSS se arregla al **imprimir** (con `e()`) y no al leer el formulario?
Porque el peligro del ataque no está en guardar lo que escribe el usuario, sino en el momento exacto en que la página web vuelve a mostrarlo en pantalla. Si cambiamos el texto al leerlo, estropearíamos la información original que el usuario quiso escribir.

4. ¿Por qué en producción `display_errors` va a `Off`, pero `log_errors` a `On`? ¿Qué aprendería un atacante de un error en pantalla?
`display_errors` se apaga para que los fallos no salgan en la pantalla de la web y el usuario no vea rutas internas o fallos del servidor. `log_errors` se enciende para que esos fallos se guarden de forma oculta en un archivo privado, permitiendo que los administradores los revisen después.

5. Explica de dónde sale tu `pm.max_children`. ¿Qué pasa si te quedas corto? ¿Y si te pasas?
Sale de restar a la memoria total del servidor lo que gasta el sistema, y dividir lo que sobra entre lo que consume cada trabajador de PHP. Si te quedas corto, los usuarios se quedarán esperando y la web irá lenta; si te pasas, la memoria RAM se llenará por completo y el servidor se bloqueará o cerrará programas.
