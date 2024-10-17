# Woo Importer Product

Este plugin permite la autenticación de usuarios a través de una API externa ([FakeStore API](https://fakestoreapi.com/auth/login)) y añade un botón para importar productos desde la API de productos de FakeStore ([https://fakestoreapi.com/products](https://fakestoreapi.com/products)) al catálogo de WooCommerce.

## Características

- Autenticación de usuarios utilizando la API FakeStore.
- Importación de productos de FakeStore API a WooCommerce solo para usuarios autenticados.
- Uso de Bootstrap 5 para la maquetación de formularios y notificaciones.
- Mensajes de logs en un archivo y notificacion visual con un Toast de Bootstrap para confirmar la importación de productos (número de productos importados y actualizados).

## Requisitos

- WordPress 6.0 o superior.
- WooCommerce instalado y activo.
- PHP 7.4 o superior.

## Instalación

### Desde el panel de administración de WordPress

1. Descarga el archivo ZIP del plugin.
2. Ve a tu panel de administración de WordPress.
3. Navega a `Plugins` > `Añadir nuevo`.
4. Haz clic en `Subir plugin`.
5. Selecciona el archivo ZIP del plugin y haz clic en `Instalar ahora`.
6. Una vez instalado, haz clic en `Activar plugin`.

## Instalación desde GitHub
1. Clona el repositorio en tu máquina local, ([Link](https://github.com/chuzasjota/woo-importer)) del proyecto.
2. Copia la carpeta del plugin a tu instalación de WordPress en la ruta `/wp-content/plugins/`.
5. Ve a `Plugins` en tu panel de administración de WordPress y activa el plugin.

### Manualmente vía FTP

1. Descarga el archivo ZIP del plugin.
2. Extrae el archivo ZIP en tu computadora.
3. Conéctate a tu servidor usando un cliente FTP.
4. Sube la carpeta del plugin a la ruta `/wp-content/plugins/`.
5. Ve a `Plugins` en tu panel de administración de WordPress y activa el plugin.

## Uso

1. Una vez que el plugin esté activado, dirígete a `WooCommerce` > `Import Products`.
2. Inicia sesión utilizando las credenciales de la API FakeStore ([FakeStore Users](https://fakestoreapi.com/users)).
3. Tras iniciar sesión, verás un botón para **Importar productos**. Haz clic para iniciar la importación.
4. Una vez completada la importación, recibirás una notificación con el número de productos importados o actualizados.
5. Podrás visualizar los productos guardados tanto en la sección de `Productos` de tu panel de administración de WordPress como al navegar por la tienda de tu sitio web.
