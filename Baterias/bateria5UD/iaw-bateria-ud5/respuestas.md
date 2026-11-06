# Explícalo con tus palabras

Cuando tengas el comprobador en verde, contesta aquí sin mirar los apuntes (2–4 líneas cada una).
Es lo que te preguntarán en la defensa de la práctica.
1. ¿Por qué después de cambiar `app.py` hay que reiniciar la app, y con PHP no hacía falta?
Python carga el código en memoria RAM al arrancar el servidor (Gunicorn) y se queda ejecutándose de forma persistente para atender peticiones de inmediato. En cambio, en entornos clásicos de PHP, los scripts se interpretan o comprueban de forma dinámica en cada petición.

2. ¿Qué hace `try_files $uri @app;`? ¿Quién sirve `estilo.css` y quién la portada?
Ordena a Nginx buscar primero si el fichero solicitado existe físicamente en el disco; si no está, deriva la petición al bloque `@app` (Gunicorn). Por tanto, `estilo.css` lo sirve directamente Nginx por rendimiento, mientras que la portada la genera dinámicamente Python.

3. ¿Para qué sirven `X-Forwarded-For` y `X-Forwarded-Proto`? ¿Qué cree la app si faltan?
Permiten que la aplicación conozca la IP real del cliente y el protocolo original (`https`) que utilizó a través del proxy inverso. Si faltan, la app creerá erróneamente que todas las peticiones provienen de `127.0.0.1` mediante HTTP plano.

4. La cookie de sesión se puede leer con `base64 -d`. Entonces, ¿por qué no se puede falsificar? ¿Qué pasa si la `SECRET_KEY` está escrita en el código y el código está en GitHub?
No se puede falsificar porque la cookie incluye una firma criptográfica generada mediante la `SECRET_KEY`, que valida su integridad. Si la clave se sube a GitHub, cualquier atacante podría firmar cookies modificadas y suplantar la identidad de usuarios privilegiados.

5. ¿Por qué en el servidor `FLASK_DEBUG` va a 0? ¿Dónde miras el error entonces?
Va a 0 para impedir que el modo depuración muestre trazas internas y la consola interactiva en el navegador, lo cual comprometería la seguridad en producción. Si ocurre un fallo, las incidencias se consultan en los ficheros de registro del servidor (como `logs/gunicorn-errores.log`).
