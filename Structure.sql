-- PASO 1: Crear los tipos de datos personalizados (ENUM)
CREATE TYPE estado_informe AS ENUM ('Pendiente', 'En Proceso', 'Resuelto', 'Cancelado');
CREATE TYPE rol_usuario AS ENUM ('Administrador', 'Tecnico', 'Usuario'); -- Ajusta los valores si son distintos

-- PASO 2: Crear las tablas usando los tipos reales
CREATE TABLE public.departamentos (
  id bigint GENERATED ALWAYS AS IDENTITY NOT NULL,
  nombre character varying NOT NULL UNIQUE,
  CONSTRAINT departamentos_pkey PRIMARY KEY (id)
);

CREATE TABLE public.usuarios (
  id bigint GENERATED ALWAYS AS IDENTITY NOT NULL,
  usuario character varying NOT NULL UNIQUE,
  contrasena_hash character varying NOT NULL,
  nombre text NOT NULL,
  rol rol_usuario NOT NULL, -- Reemplazado USER-DEFINED por rol_usuario
  cargo character varying NOT NULL,
  id_departamento bigint NOT NULL,
  CONSTRAINT usuarios_pkey PRIMARY KEY (id),
  CONSTRAINT usuarios_id_departamento_fkey FOREIGN KEY (id_departamento) REFERENCES public.departamentos(id)
);

CREATE TABLE public.informes_tecnicos (
  id bigint GENERATED ALWAYS AS IDENTITY NOT NULL,
  fecha timestamp with time zone NOT NULL DEFAULT now(),
  fecha_actualizacion timestamp with time zone,
  fecha_cierre timestamp with time zone,
  id_solicitante bigint NOT NULL,
  id_tecnico bigint,
  descripcion_equipo text NOT NULL,
  diagnostico_problema text,
  observacion text,
  estado estado_informe NOT NULL DEFAULT 'Pendiente', -- Reemplazado USER-DEFINED por estado_informe
  CONSTRAINT informes_tecnicos_pkey PRIMARY KEY (id),
  CONSTRAINT informes_tecnicos_id_solicitante_fkey FOREIGN KEY (id_solicitante) REFERENCES public.usuarios(id),
  CONSTRAINT informes_tecnicos_id_tecnico_fkey FOREIGN KEY (id_tecnico) REFERENCES public.usuarios(id)
);