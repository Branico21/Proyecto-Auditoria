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

SELECT * FROM usuarios;