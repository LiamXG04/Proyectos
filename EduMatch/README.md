# EduMatch

Aplicación web adaptable para conectar estudiantes que necesitan apoyo académico con estudiantes mentores. Está construida con Laravel 10, Blade y CSS, a partir del MVP descrito en la documentación del proyecto.

## Alcance del MVP

- Registro e inicio de sesión con rol de mentor o mentee.
- Perfil académico y selección de materias que se enseñan o en las que se busca apoyo.
- Búsqueda de mentores por materia con sugerencias, coincidencia sin distinguir tildes y tolerancia a errores tipográficos; consulta de perfiles, calificaciones y horarios.
- Publicación de disponibilidad semanal por parte de los mentores.
- Solicitud de sesiones de 30 o 45 minutos, con validación de disponibilidad y conflictos de horario.
- Aceptación, cancelación y seguimiento de sesiones.
- Chat persistente por sesión y calificación al completarla.

La aplicación ofrece una experiencia web responsive para computadoras y celulares. Las videollamadas integradas, la gamificación, los certificados y el emparejamiento automático avanzado quedan fuera de esta primera versión, tal como se plantean como mejoras futuras en la documentación. El chat es persistente, pero no se actualiza en tiempo real.

## Requisitos

- PHP 8.1 o superior y Composer.
- Una base de datos compatible con Laravel (MySQL o SQLite).

## Puesta en marcha

1. Instala dependencias de PHP:

   ```bash
   composer install
   ```

2. Copia `.env.example` a `.env` y configura la conexión a MySQL (host, usuario y contraseña). Crea el esquema `edumatch` en el servidor MySQL si aún no existe:

   ```sql
   CREATE DATABASE edumatch CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

   Confirma en `.env` que `DB_CONNECTION=mysql` y `DB_DATABASE=edumatch`. Si prefieres SQLite, crea `database/database.sqlite` y configura `DB_CONNECTION=sqlite` y `DB_DATABASE=database/database.sqlite`.

3. Genera la clave de la aplicación y crea las tablas y materias iniciales:

   ```bash
   php artisan key:generate
   php artisan migrate --seed
   ```

4. Inicia el servidor:

   ```bash
   php artisan serve
   ```

5. Abre la dirección local indicada por Artisan y crea una cuenta de mentor y otra de mentee para probar el recorrido.

En MySQL Workbench, selecciona **Refresh** en el panel **SCHEMAS**; debe aparecer el esquema `edumatch`. La tabla `users` guarda las cuentas. El servidor MySQL debe ser el mismo indicado por `DB_HOST` y `DB_PORT` en `.env`.

## Mentores de demostración

`php artisan db:seed --class=MentorDemoSeeder` agrega seis perfiles ficticios de mentor con materias y horarios de disponibilidad. `php artisan db:seed --class=MentorDemoReviewSeeder` agrega dos reseñas ficticias por mentor, asociadas a sesiones completadas de demostración. La segunda carga también crea cuentas mentee ficticias para vincular las reseñas. Ambas cargas se pueden repetir sin duplicar cuentas, sesiones ni reseñas.

Todos usan la contraseña `MentorDemo2026!`:

- `laura.mendez@edumatch.test`
- `carlos.ramirez@edumatch.test`
- `mariana.flores@edumatch.test`
- `diego.hernandez@edumatch.test`
- `sofia.castillo@edumatch.test`
- `andres.molina@edumatch.test`

Estos perfiles y credenciales son únicamente para la base local de demostración; no los uses en un despliegue público.

Las reseñas ficticias y las cuentas que las publican usan las mismas credenciales de demostración. La página del mentor las identifica como ejemplos, no como opiniones reales.

No se requiere un proceso de compilación de JavaScript para las vistas de esta versión.

## Pruebas

```bash
php artisan test
```
