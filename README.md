# Proyecto Auditoria y Seguridad 

## Desarrollado por:
- Yesid Mauricio Bello Jimenez
- Brayan Nicolas Robayo Baquero
- Juan Sebastian Sanchez Parra 

## Instalación
Clona el repositorio:
```bash
    git clone https://github.com/Branico21/Proyecto-Auditoria.git
    cd proyecto
```
 Instala las dependencias con **Composer**:
```bash
    composer install
```
Configura el archivo `.env` en la raíz del proyecto con los datos de tu base de datos PostgreSQL:
```
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=nombre_basedatos
DB_USERNAME=usuario
DB_PASSWORD=contraseña
```
Crea la base de datos en **PostgreSQL**:
```
CREATE TABLE usuarios (
    id SERIAL PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    tipo_documento VARCHAR(10) NOT NULL,
    documento VARCHAR(20) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    direccion VARCHAR(200) NOT NULL,
    rol VARCHAR(20) NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP
);

CREATE INDEX idx_username ON usuarios(username);
CREATE INDEX idx_email ON usuarios(email);
CREATE INDEX idx_tipo_documento_documento ON usuarios(tipo_documento, documento);

-- Restricción única para tipo de documento y número
ALTER TABLE usuarios ADD CONSTRAINT unique_tipo_documento_documento UNIQUE (tipo_documento, documento);

CREATE TABLE inventario (
    id_inventario SERIAL PRIMARY KEY,
    marca VARCHAR(50) NOT NULL,
    modelo VARCHAR(50) NOT NULL,
    serial VARCHAR(50) UNIQUE NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    estado VARCHAR(50) NOT NULL,
    id_persona INT -- FOREIGN KEY opcional si tienes una tabla de personas
);
```
Inicia el servidor local con **XAMPP**:
   - Asegúrate de que Apache y PostgreSQL estén corriendo.
   - Coloca tu proyecto en la carpeta `htdocs` de XAMPP.

Accede en el navegador a: [http://localhost/proyecto](http://localhost/proyecto)

Tecnologías
- **PHP** - Lenguaje principal
- **Composer** - Gestión de dependencias
- **PostgreSQL** - Base de datos
- **XAMPP o Laragon** - Servidor local