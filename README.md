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
- **Frontend**: **Vue.js 3** + **Axios** + **Bootstrap 5.3**.
- **Base de Datos**: MySQL/MariaDB con soporte UTF8mb4.

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
3. Si su servidor MySQL tiene credenciales específicas (contraseña, usuario, host o puerto diferente), puede crear un archivo `.env` a partir de [.env.example](.env.example) o ajustarlas en [config/database.php](config/database.php).

### 4. Iniciar el Servidor de Desarrollo
Puede iniciar el servidor web embebido de PHP apuntando a la carpeta `public`:
```bash
php -S localhost:8000 -t public
```

Luego abra su navegador web en:
👉 **http://localhost:8000** (o **http://localhost:8000/ofertas**)
