# ARUS SYSTEM

Proyecto desarrollado como Trabajo Final del Ciclo Formativo de Grado Superior en Desarrollo de Aplicaciones Multiplataforma (DAM).

ARUS SYSTEM es una plataforma web orientada a la gestión de servicios tecnológicos. El objetivo del proyecto es desarrollar una aplicación web completa aplicando buenas prácticas de programación, organización del código y desarrollo progresivo mediante el uso de Git y GitHub.

## Objetivos del proyecto

- Desarrollar una aplicación web completa utilizando tecnologías actuales del entorno web.
- Aplicar los conocimientos adquiridos durante el CFGS de Desarrollo de Aplicaciones Multiplataforma.
- Implementar una arquitectura organizada, escalable y fácil de mantener.
- Profundizar en el desarrollo backend con PHP y la gestión de bases de datos MySQL.
- Utilizar Git y GitHub para llevar un control de versiones siguiendo un desarrollo progresivo.

## Tecnologías

### Frontend
- HTML5
- CSS3
- JavaScript (ES6+)

### Backend
- PHP

### Base de datos
- MySQL

### Herramientas
- Git
- GitHub
- Visual Studio Code
- XAMPP

## Configuración de la base de datos

La aplicación utiliza PHP para ejecutar la lógica del servidor y MySQL como sistema de gestión de bases de datos. No son tecnologías alternativas: PHP procesa las peticiones de la aplicación y se comunica con MySQL para consultar, insertar, actualizar y eliminar información.

La conexión se realiza mediante PDO (PHP Data Objects), ya que proporciona una interfaz segura y consistente para trabajar con bases de datos. Además, permite utilizar consultas preparadas, reduciendo el riesgo de inyección SQL y facilitando el mantenimiento del código.

El archivo `config/db.php` contiene las credenciales reales de conexión y está excluido del repositorio mediante `.gitignore`. Para facilitar la instalación del proyecto sin exponer datos privados, se incluye `config/db.example.php` como plantilla. Para configurar la aplicación, se debe copiar este archivo como `config/db.php` y sustituir los valores de ejemplo por las credenciales del entorno local.

### Catálogos auxiliares

Además de las tablas principales de la aplicación, la base de datos incorpora tablas de apoyo destinadas a centralizar información reutilizable. Entre ellas se encuentra `prefijos_telefonicos`, que almacena los países, sus prefijos internacionales, las banderas correspondientes y el orden de visualización. Esta información se reutiliza dinámicamente para generar el selector de prefijos telefónicos en los distintos formularios de la aplicación, evitando mantener listas estáticas dentro del código.

## Estructura del proyecto

```text
ProyectoDAM/
├── admin/
│   ├── dashboard.php
│   ├── estadisticas.php
│   ├── planes.php
│   ├── servicios.php
│   ├── usuario-detalle.php
│   ├── usuarios.php
│   ├── worker-detalle.php
│   └── workers.php
│
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── img/
│   │   ├── robot.png
│   │   ├── robot_centre.png
│   │   ├── robot_down.png
│   │   ├── robot_left.png
│   │   ├── robot_postura.png
│   │   ├── robot_right.png
│   │   ├── robot_up.png
│   │   ├── left.png
│   │   ├── centre.png
│   │   ├── right.png
│   │   ├── up.png
│   │   ├── down.png
│   │   └── 0 Principal.png
│   └── js/
│       └── main.js
│
├── candidato/
│   ├── candidatura.php
│   ├── dashboard.php
│   ├── perfil.php
│   └── tickets.php
│
├── cliente/
│   ├── contratos.php
│   ├── dashboard.php
│   ├── facturas.php
│   ├── perfil.php
│   ├── presupuestos.php
│   ├── proyectos.php
│   └── tickets.php
│
├── config/
│   ├── config.php
│   ├── db.example.php
│   └── db.php
│
├── includes/
│   ├── auth.php
│   ├── footer.php
│   ├── header.php
│   └── validaciones.php
│
├── public/
│   ├── candidatura.php
│   ├── crear-password.php
│   ├── login.php
│   ├── recuperar-password.php
│   └── solicitar-servicio.php
│
├── uploads/
│   ├── contratos/
│   ├── cv/
│   └── facturas/
│
├── worker/
│   ├── candidaturas.php
│   ├── dashboard.php
│   ├── proyectos.php
│   └── tickets.php
│
├── .gitignore
├── index.php
└── README.md
```

### Recursos gráficos

Actualmente la aplicación utiliza únicamente la imagen principal del robot como elemento gráfico integrado en la interfaz. El resto de imágenes almacenadas en `assets/img/` se conservan de forma temporal como recursos de apoyo para futuras mejoras visuales, animaciones o posibles variantes del diseño.

Algunos recursos gráficos, como los logotipos definitivos del proyecto, no forman parte del repositorio público por tratarse de elementos de identidad visual actualmente en desarrollo.

En caso de que estas imágenes no resulten necesarias durante el desarrollo del proyecto, serán eliminadas para mantener el repositorio limpio y contener únicamente los archivos realmente utilizados por la aplicación.

### Organización del proyecto

| Carpeta | Descripción |
|---------|-------------|
| `admin/` | Panel de administración y gestión del sistema. |
| `assets/` | Recursos estáticos (CSS, JavaScript e imágenes). |
| `candidato/` | Área privada para los candidatos registrados. |
| `cliente/` | Área privada para los clientes. |
| `config/` | Configuración general y conexión con la base de datos. |
| `includes/` | Componentes reutilizables y funciones comunes. |
| `public/` | Páginas públicas accesibles sin autenticación. |
| `uploads/` | Archivos subidos por los usuarios. |
| `worker/` | Área privada para los trabajadores del sistema. |

### Componentes reutilizables

Los archivos `includes/header.php` e `includes/footer.php` contienen la estructura común que comparten las distintas páginas de la aplicación. Se mantienen separados del contenido de cada página para evitar repetir el mismo código HTML y facilitar el mantenimiento del proyecto.

El archivo `includes/validaciones.php` centraliza las funciones de validación reutilizables para documentos identificativos. Actualmente contiene la validación de DNI, NIE y CIF, permitiendo que formularios como la solicitud de servicios y la candidatura compartan la misma lógica sin duplicar código. Cada formulario utiliza únicamente las validaciones que necesita.

Cada página puede incorporar estos componentes mediante `require`, de forma que cualquier cambio futuro en la cabecera o en el pie de página se realice una sola vez y se aplique automáticamente a todas las landing pages que los utilicen. Esto permite mantener una estructura visual coherente y evita tener que modificar cada página de forma individual.


```php
<?php require_once __DIR__ . '/includes/header.php'; ?>

<!-- Contenido específico de la página -->

<?php require_once __DIR__ . '/includes/footer.php'; ?>
```

### Formularios públicos

El formulario `public/solicitar-servicio.php` permite registrar una solicitud de servicio y crear inicialmente la cuenta asociada al cliente. Utiliza las validaciones comunes de DNI, NIE y CIF, consulta dinámicamente los prefijos telefónicos almacenados en la base de datos y normaliza el teléfono antes de almacenarlo.

El formulario `public/candidatura.php` permite iniciar el registro de un candidato utilizando DNI o NIE. Tras validar los datos, crea el usuario con rol `candidato` y registra su candidatura en la tabla `candidaturas` dentro de una misma transacción. La candidatura almacena la dirección y la presentación inicial del candidato, mientras que la carga del currículum se realizará posteriormente desde su área privada.

Ambos formularios reutilizan `includes/validaciones.php`, evitando duplicar las funciones de validación de documentos y manteniendo esta lógica centralizada.

### Página principal

El archivo `index.php` constituye el punto de entrada público de ARUS SYSTEM y actúa como la landing page principal de la aplicación. Desde esta página se presenta el proyecto, los servicios ofrecidos y los planes de mantenimiento disponibles.

La información mostrada en los apartados de servicios y planes se obtiene dinámicamente desde la base de datos MySQL mediante PDO, evitando mantener el contenido fijo en el código fuente y facilitando su actualización desde la base de datos.

Para garantizar una salida segura, todos los datos procedentes de la base de datos se muestran utilizando funciones como `htmlspecialchars()` y `number_format()`, reduciendo riesgos de inyección de contenido y asegurando un formato consistente.

La página reutiliza los componentes `includes/header.php` e `includes/footer.php` mediante `require`, lo que evita duplicar código HTML y permite que cualquier modificación futura en la cabecera o el pie de página se aplique automáticamente a todas las páginas que compartan dichos componentes.

### Hoja de estilos principal

El archivo `assets/css/style.css` centraliza toda la apariencia visual de la aplicación. Define la identidad gráfica de ARUS SYSTEM mediante una única hoja de estilos organizada por secciones, facilitando el mantenimiento y la evolución del diseño.

La estructura del archivo agrupa los estilos correspondientes al encabezado, la portada principal, las secciones de servicios y planes, los formularios, el pie de página y el comportamiento responsive, manteniendo una organización coherente y fácil de localizar.

Además del diseño visual, la hoja de estilos incorpora variables CSS para reutilizar colores y valores comunes, así como transiciones, efectos de interacción y adaptaciones para distintos tamaños de pantalla, mejorando la experiencia de usuario y simplificando futuras modificaciones.

La hoja de estilos también incorpora el diseño del selector internacional de prefijos telefónicos utilizado por los formularios públicos. El selector y el campo del número se presentan como un único control visual, manteniendo una apariencia uniforme, adaptable a dispositivos móviles y reutilizable en futuros formularios de la aplicación.

### Lógica JavaScript principal

El archivo `assets/js/main.js` concentra el comportamiento dinámico de la aplicación. Su código está organizado en módulos independientes mediante funciones autoejecutables (IIFE), de forma que cada bloque implementa una única responsabilidad sin contaminar el ámbito global.

Entre las funcionalidades implementadas se encuentran el efecto parallax de la portada, la animación interactiva del robot siguiendo el movimiento del cursor, la aparición progresiva de los elementos durante el desplazamiento de la página mediante `IntersectionObserver` y la gestión del formulario modal de inicio de sesión.

El sistema de autenticación se integra directamente sobre la página principal mediante JavaScript, que intercepta el botón **Iniciar sesión** para mostrar un formulario emergente sin abandonar la landing page. El modal puede cerrarse mediante el botón de cierre, pulsando sobre el fondo oscurecido o utilizando la tecla `Escape`, proporcionando una experiencia de usuario más fluida.

Esta organización facilita el mantenimiento del código, mejora la legibilidad y permite ampliar la funcionalidad de la aplicación sin afectar al resto de componentes.

### Sistema de autenticación

El archivo `includes/auth.php` centraliza las funciones relacionadas con la autenticación y el control de acceso de la aplicación. Su objetivo es evitar duplicar lógica entre las distintas áreas privadas y proporcionar un único punto de mantenimiento para la gestión de sesiones.

Entre sus responsabilidades se incluyen el inicio y cierre de sesión, la comprobación del estado de autenticación, la protección de páginas privadas y la validación de roles de usuario. Además, incorpora medidas de seguridad como la regeneración del identificador de sesión tras el inicio de sesión y la correcta destrucción de la sesión durante el cierre.

Esta organización facilita reutilizar la misma lógica de autenticación en toda la aplicación, mejora la mantenibilidad del proyecto y favorece una gestión de accesos consistente.

### Sistema de inicio de sesión

El sistema de autenticación se distribuye entre los archivos `public/login.php`, `index.php`, `includes/auth.php`, `includes/header.php`, `includes/footer.php`, `assets/js/main.js` y `assets/css/style.css`, permitiendo integrar el acceso de usuarios directamente sobre la página principal mediante un formulario modal.

Al pulsar el botón **Iniciar sesión**, JavaScript intercepta la navegación y muestra un formulario emergente centrado sobre la landing page sin abandonar la página actual. El formulario solicita el rol del usuario, su dirección de correo electrónico y la contraseña.

El archivo `public/login.php` actúa exclusivamente como procesador de autenticación. Valida los datos recibidos, comprueba el formato del correo electrónico, verifica que el rol seleccionado sea válido y consulta la base de datos mediante sentencias preparadas con PDO. La contraseña se valida utilizando `password_verify()` sobre el hash almacenado en la base de datos.

Cuando las credenciales son correctas, la aplicación inicia la sesión mediante las funciones centralizadas en `includes/auth.php`, actualiza la fecha de la última actividad del usuario y redirige automáticamente al panel correspondiente según su rol (`admin`, `cliente`, `worker` o `candidato`).

Si la autenticación falla, el sistema almacena temporalmente un mensaje genérico y los datos necesarios en variables de sesión, vuelve automáticamente a la página principal y reabre el formulario modal. Este comportamiento evita revelar qué dato concreto es incorrecto, reforzando la seguridad frente a posibles intentos de enumeración de usuarios.

El archivo `includes/header.php` mantiene una ruta real hacia `public/login.php` como alternativa si JavaScript no se carga, mientras que `includes/footer.php` incorpora una versión dinámica del archivo JavaScript mediante `filemtime()` para evitar problemas de caché durante el desarrollo.

La gestión de sesiones, el control de acceso y la protección de las áreas privadas permanecen centralizados en `includes/auth.php`, evitando duplicar lógica entre los distintos módulos de la aplicación y facilitando el mantenimiento del proyecto.



## Estado actual

- ✅ Arquitectura del proyecto definida.
- ✅ Entorno de desarrollo configurado.
- 🚧 Desarrollo en curso.

## Roadmap

Este proyecto se desarrolla de forma progresiva mediante el uso de Git y GitHub. Cada fase del Roadmap representa un conjunto de funcionalidades que se incorporarán al proyecto y quedarán reflejadas en el historial de commits.

### Fase 1 · Preparación del proyecto

- [x] Crear la documentación inicial (`README.md`).
- [x] Configurar Git y GitHub para el control de versiones.

### Fase 2 · Configuración

- [x] Configurar la conexión privada con la base de datos (`db.php`).
- [x] Crear el archivo de ejemplo para la configuración (`db.example.php`).
- [ ] Implementar la configuración global del proyecto (`config.php`).

### Fase 3 · Base de la aplicación

- [x] Crear los componentes reutilizables (`header.php` y `footer.php`).
- [x] Desarrollar la página principal (`index.php`).
- [x] Diseñar la hoja de estilos principal (`style.css`).
- [x] Implementar la lógica JavaScript (`main.js`).
- [x] Implementar el sistema de autenticación (`auth.php`).

### Fase 4 · Zona pública

- [x] Desarrollar el formulario de solicitud de servicios.
- [x] Implementar el sistema de acceso (login).
- [ ] Implementar la recuperación y creación de contraseña.
- [x] Desarrollar el formulario de candidaturas.

### Fase 5 · Paneles privados

- [ ] Desarrollar el área de administración.
- [ ] Desarrollar el área de clientes.
- [ ] Desarrollar el área de trabajadores.
- [ ] Desarrollar el área de candidatos.

### Fase 6 · Mejoras

- [ ] Integrar completamente la base de datos.
- [ ] Implementar validaciones y medidas de seguridad.
- [ ] Optimizar el rendimiento de la aplicación.
- [ ] Mejorar la experiencia de usuario (UX).
- [ ] Preparar la versión 1.0.