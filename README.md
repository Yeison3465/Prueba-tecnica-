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

> **Nota sobre el Frontend:** La SPA de Vue.js 3 ya se encuentra previamente compilada en `public/dist/`, por lo que **no es obligatorio tener Node.js instalado** para evaluar el proyecto.
> 
> Si desea ejecutar el frontend en modo desarrollo con recarga en vivo (HMR):
> ```bash
> cd frontend
> npm install
> npm run dev
> ```

---

## 📋 Requisitos de la Prueba Implementados

1. **Gestión de Licitaciones (CRUD):** Creación, listado con filtros, edición y vista de detalle.
2. **Consecutivo Automático y Concurrente:** Formato `PO-XXXX-AA` (ej. `PO-0001-26`) con control de concurrencia pesimista (`GET_LOCK`) y reintentos automáticos.
3. **Catálogo UNSPSC:** Búsqueda autocompletable con debounce conectada a más de 3.000 actividades clasificadas por segmento, familia, clase y producto.
4. **Control de Cronograma y Cierre Automático:** Validación estricta de fechas/horas y transición automática de estado a `Cerrada` al vencer el plazo límite.
5. **Gestión Documental:** Carga y validación exclusiva de archivos PDF o ZIP (hasta 10 MB) con almacenamiento seguro y nombres con hash criptográfico.
6. **Regla Obligatoria en Edición:** Validación dual (frontend y backend) que exige al menos un documento adjunto para permitir el guardado.
7. **Exportación a Excel:** Generación nativa con `PhpSpreadsheet` respetando los filtros de búsqueda y omitiendo la paginación.

