# Carnicería - Sistema de Gestión

Este proyecto es una aplicación web desarrollada en Laravel para la gestión de productos, categorías, cortes y ventas en una carnicería.

## Características

-   Autenticación de usuarios (registro, login, recuperación de contraseña)
-   Gestión de productos, categorías y cortes
-   Registro y visualización de ventas
-   Panel de análisis y estadísticas
-   Impresión de recibos
-   Edición de perfil de usuario

## Requisitos

-   PHP >= 8.1
-   Composer
-   Node.js y npm
-   Base de datos MySQL/MariaDB

## Instalación

1. Clona el repositorio:

    ```bash
    git clone https://github.com/Nixus32xD/carniceria
    cd carniceria
    ```

2. Instala dependencias de PHP:

    ```bash
    composer install
    ```

3. Instala dependencias de JavaScript:

    ```bash
    npm install
    ```

4. Copia el archivo de entorno y configura tus variables:

    ```bash
    cp .env.example .env
    ```

5. Genera la clave de la aplicación:

    ```bash
    php artisan key:generate
    ```

6. Ejecuta las migraciones:

    ```bash
    php artisan migrate --seed
    ```

7. Inicia el servidor de desarrollo:
    ```bash
    php artisan serve
    ```
8. Inicia el servidor de estilos:
    ```bash
    npm run dev
    ```

## Uso

Accede a la aplicación en [http://localhost:8000](http://localhost:8000) y registra un usuario para comenzar a gestionar la carnicería.

Principalmente no se muestran las rutas `/register` y `/login` pero estan disponibles para crear una cuenta o logearse

## Estructura de rutas

-   `/` - Dashboard principal (requiere autenticación)
-   `/dashboard/products` - Gestión de productos
-   `/dashboard/categories` - Gestión de categorías
-   `/dashboard/cuts` - Gestión de cortes
-   `/dashboard/sales` - Registro y visualización de ventas
-   `/dashboard/analytics` - Análisis y estadísticas
-   `/profile` - Edición de perfil

## Uso de los formularios

La aplicación incluye varios formularios para gestionar productos, ventas, cortes y categorías.  
A continuación se explica cómo utilizarlos y qué hace cada vista principal:

### Dashboard

La vista **Dashboard** es la página principal tras iniciar sesión.  
Aquí se muestra un resumen general de la carnicería, como estadísticas rápidas, productos destacados y accesos a las diferentes secciones de gestión.

### Products

En la sección **Products** puedes:

-   **Agregar producto:** Completa el formulario con nombre, categoría, corte, precio y stock. Haz clic en "Guardar" para registrar el producto.
-   **Editar producto:** Haz clic en el botón de edición junto al producto, modifica los datos (incluyendo categoría y corte) y guarda los cambios.
-   **Eliminar producto:** Usa el botón de eliminar para quitar un producto de la lista.

-   **Categoría:** Selecciona la categoría del producto (por ejemplo: Vacuno, Cerdo, Pollo, etc.).
-   **Corte:** Selecciona el tipo de corte correspondiente (por ejemplo: Bife, Costilla, Lomo, etc.).

Las categorías y cortes permiten organizar y clasificar los productos para una gestión más eficiente.

### Sales

En la sección **Sales** puedes:

-   **Registrar una venta:** Selecciona productos, cantidades y completa los datos del cliente si es necesario. Haz clic en "Registrar venta" para guardar la operación.
-   **Ver detalles de venta:** Haz clic en una venta para ver el detalle de los productos vendidos, el total y la fecha.
-   **Imprimir recibo:** Usa el botón de imprimir para generar un recibo de la venta.

### Analytics

La vista **Analytics** muestra gráficos y estadísticas sobre las ventas, productos más vendidos, ingresos y tendencias.  
No tiene formularios, solo visualización de datos para ayudar en la toma de decisiones.

---

**Nota:**  
Los formularios de login y registro no se muestran en el menú principal, pero están disponibles en las rutas `/login` y `/register` si necesitas crear una cuenta o iniciar sesión.

## Licencia

Este proyecto es propiedad de Nicolas Moron  
Queda prohibida la copia, distribución o modificación del código sin autorización expresa del autor.
