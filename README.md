# Sistema de Gestión de Licitaciones y Ofertas en Línea

> **Prueba Técnica – Desarrollador FullStack Junior (Suplos)**  
> Desarrollado con **PHP 8.2 (sin frameworks)** bajo arquitectura **MVC**, utilizando **Eloquent ORM Standalone** para la persistencia en base de datos, y un frontend reactivo con **Vue.js**, **Bootstrap 5.2+** y **Axios**.

---

## 🚀 Requisitos del Sistema

- **PHP 7.4+ o PHP 8.0+** (Probado y compatible con PHP 8.2).
- Extensiones PHP requeridas: `pdo_mysql`, `zip`, `mbstring`, `fileinfo`.
- **Composer 2.x**.
- **MySQL 5.7+ o MariaDB 10.3+** (ej. XAMPP, Laragon o MySQL nativo).

---

## 🛠️ Tecnologías y Arquitectura

- **Backend**: PHP puro sin frameworks (sin Laravel, CodeIgniter ni Symfony).
- **Patrón Arquitectónico**: Modelo - Vista - Controlador (MVC) estricto con *Front Controller* (`public/index.php`) y enrutador RESTful limpio.
- **ORM**: **Eloquent Standalone** (`illuminate/database` + `illuminate/events`) a través de la clase `Capsule`, garantizando consultas preparadas seguras, prevención de inyecciones SQL y relaciones (`belongsTo`, `hasMany`).
- **Exportación Excel**: **PhpSpreadsheet** (`phpoffice/phpspreadsheet`) para generar archivos `.xlsx` nativos con encabezados estilizados y formateo contable desde el backend.
- **Frontend**: **Vue.js 3** + **Axios** + **Bootstrap 5.3** + **SweetAlert2**.
- **Base de Datos**: MySQL/MariaDB con soporte UTF8mb4.

---

## 📂 Estructura del Proyecto

```text
├── app/
│   ├── Controllers/
│   │   ├── BaseController.php       # Respuestas JSON normalizadas y render de vistas
│   │   ├── OfertaController.php     # Lógica CRUD, validaciones y subida de archivos
│   │   ├── ActividadController.php  # API para listar actividades del clasificador UNSPSC
│   │   └── ReporteController.php    # Exportación nativa a Excel (.xlsx) sin paginar
│   ├── Core/
│   │   ├── Router.php               # Enrutador HTTP liviano con soporte de parámetros y métodos
│   │   ├── Database.php             # Configuración e inicio de Eloquent Capsule
│   │   └── Validator.php            # Reglas de negocio y validaciones del backend
│   ├── Models/
│   │   ├── Oferta.php               # Modelo Eloquent con relaciones a actividad y documentos
│   │   ├── Actividad.php            # Modelo Eloquent para el clasificador UNSPSC
│   │   └── OfertaDocumento.php      # Modelo Eloquent para los archivos adjuntos
│   └── Helpers/
│       └── ConsecutivoHelper.php    # Generador automático del consecutivo PO-0001-YY
├── bd/
│   ├── schema.sql                   # Estructura DDL de base de datos e índices
│   ├── actividades_seed.sql         # Seed con casi 3,000 actividades reales UNSPSC
│   └── import_unspsc.php            # Script PHP de importación desde el Excel de Storyblok
├── config/
│   └── database.php                 # Parámetros de conexión a la base de datos
├── public/
│   ├── assets/
│   │   ├── css/app.css              # Estilos corporativos Suplos
│   │   └── js/
│   │       ├── listado-ofertas.js   # Vue: búsqueda en tiempo real, filtros y paginación
│   │       ├── form-oferta.js       # Vue: contadores de caracteres, validación cruzada y documentos
│   │       └── detalle-oferta.js    # Vue: vista consolidada con pestañas (Tabs)
│   ├── uploads/                     # Almacenamiento seguro de archivos PDF y ZIP
│   ├── .htaccess                    # Reglas mod_rewrite para servidores Apache
│   └── index.php                    # Front Controller (Punto de entrada único)
├── views/
│   ├── layouts/
│   │   ├── header.php               # Estructura HTML común, Bootstrap 5, FontAwesome y Vue 3
│   │   └── footer.php               # Cierre de vistas y scripts globales
│   └── ofertas/
│       ├── index.php                # Vista de listado
│       ├── create.php               # Formulario de creación (Info Básica + Cronograma)
│       ├── edit.php                 # Formulario de edición (+ Sección de Documentos)
│       └── show.php                 # Vista de detalle con navegación por pestañas
├── composer.json                    # Declaración de dependencias del proyecto
└── README.md                        # Documentación e instrucciones de instalación
```

---

## ⚙️ Pasos de Instalación y Puesta en Marcha

### 1. Clonar o Descargar el Proyecto
Asegúrese de encontrarse en el directorio raíz del proyecto:
```bash
cd "Prueba  tecnica"
```

### 2. Instalar Dependencias de Composer
Ejecutar en la terminal:
```bash
composer install
```

### 3. Configurar la Base de Datos
1. Inicie su servidor de base de datos MySQL / MariaDB (por ejemplo, desde el panel de **XAMPP** haciendo clic en **Start** en MySQL).
2. Cree la base de datos e importe los esquemas y datos iniciales:
   - Puede hacerlo desde **phpMyAdmin** ejecutando los scripts en este orden:
     1. `bd/schema.sql` (Crea la base de datos `licitaciones_db` y las tablas `actividades`, `ofertas` y `ofertas_documentos`).
     2. `bd/actividades_seed.sql` (Inserta las actividades del clasificador de bienes y servicios UNSPSC).
   - O por línea de comandos (MySQL):
     ```bash
     mysql -u root -p < bd/schema.sql
     mysql -u root -p licitaciones_db < bd/actividades_seed.sql
     ```
3. Si su MySQL tiene una contraseña distinta a vacío `""` o usuario diferente a `root`, ajústelo en [config/database.php](config/database.php).

### 4. Iniciar el Servidor de Desarrollo
Puede iniciar el servidor web embebido de PHP apuntando a la carpeta `public`:
```bash
php -S localhost:8000 -t public
```

Luego abra su navegador web en:
👉 **http://localhost:8000** (o **http://localhost:8000/ofertas**)

---

## 📋 Funcionalidades y Validaciones Implementadas

### 1. Formulario de Creación de Oferta (`/ofertas/crear`)
- **Sección 1 (Información Básica):**
  - **Objeto:** Texto obligatorio, no permite solo espacios, bloquea y valida límite a 150 caracteres con contador en vivo.
  - **Descripción / Alcance:** Textarea obligatorio, no permite solo espacios, límite a 400 caracteres con contador en vivo.
  - **Moneda:** Select con opciones fijas (`COP`, `USD`, `EUR`).
  - **Presupuesto:** Número decimal positivo mayor a cero con hasta 2 decimales.
  - **Actividad:** Select dinámico que consume asíncronamente vía Axios `/api/actividades` desde la base de datos (con estado de carga deshabilitado).
- **Sección 2 (Cronograma):**
  - Validación cruzada de fechas y horas: `fecha_cierre` $\ge$ `fecha_inicio`, y si coinciden en el mismo día, `hora_cierre` $>$ `hora_inicio`.
- **Botón Guardar:** Permanece **deshabilitado** hasta que todas las condiciones se cumplen satisfactoriamente.
- **Consecutivo Automático:** Generado por el backend con formato `PO-{000X}-{YY}` (ej. `PO-0001-26`).

### 2. Formulario de Edición de Oferta (`/ofertas/editar/{id}`)
- Precarga reactiva de los datos existentes en las Secciones 1 y 2.
- **Sección 3 (Documentos):**
  - Botón para desplegar modal de carga de documentos.
  - Validación de extensiones permitidas: **únicamente PDF o ZIP**.
  - **Regla obligatoria:** En modo edición es estrictamente obligatorio contar con **al menos 1 documento cargado**. El botón de guardado final bloquea la acción si no existe documentación adjunta.

### 3. Listado de Ofertas (`/ofertas`)
- Visualización de ofertas con consecutivo, objeto, descripción, fechas de inicio y cierre, estado y acciones.
- Filtros reactivos en tiempo real por **consecutivo**, **objeto** y **descripción**.
- Paginación interactiva.
- **Botón "Descargar Reporte Excel":** Genera desde el backend mediante PhpSpreadsheet un archivo nativo `.xlsx` con todas las licitaciones filtradas (sin paginación).

### 4. Vista de Detalle (`/ofertas/detalle/{id}`)
- Interfaz organizada mediante pestañas (Tabs Bootstrap 5):
  - **Información Básica** (con datos desglosados del clasificador UNSPSC).
  - **Cronograma** (con tarjetas visuales de apertura y cierre).
  - **Documentos Adjuntos** (tabla con enlaces de descarga de los archivos PDF/ZIP).
- Botón directo para ingresar al modo de edición.

---

## 👨‍💻 Autor
Proyecto desarrollado para la evaluación técnica de desarrolladores FullStack Junior.
