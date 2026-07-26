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
│   │   ├── logo.png
│   │   ├── logo_original.png
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
│   └── header.php
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

- [ ] Implementar el sistema de autenticación (`auth.php`).
- [ ] Crear los componentes reutilizables (`header.php` y `footer.php`).
- [ ] Desarrollar la página principal (`index.php`).
- [ ] Diseñar la hoja de estilos principal (`style.css`).
- [ ] Implementar la lógica JavaScript (`main.js`).

### Fase 4 · Zona pública

- [ ] Desarrollar el formulario de solicitud de servicios.
- [ ] Implementar el sistema de acceso (login).
- [ ] Implementar la recuperación y creación de contraseña.
- [ ] Desarrollar el formulario de candidaturas.

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