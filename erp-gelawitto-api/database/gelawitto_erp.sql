--
-- PostgreSQL database dump
--

\restrict aRICirSsDwaaa7bGrdVIuuX8vUBh5IbVipyWIhtcv5k94nSQJPMWygFhTZXWfLN

-- Dumped from database version 16.15 (Debian 16.15-1.pgdg13+2)
-- Dumped by pg_dump version 18.6

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: asientos_contables; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.asientos_contables (
    id bigint NOT NULL,
    numero_asiento character varying(30) NOT NULL,
    fecha date NOT NULL,
    modulo_origen character varying(40) NOT NULL,
    referencia character varying(50),
    concepto character varying(200) NOT NULL,
    total_debe numeric(12,2) NOT NULL,
    total_haber numeric(12,2) NOT NULL,
    user_id bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: asientos_contables_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.asientos_contables_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: asientos_contables_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.asientos_contables_id_seq OWNED BY public.asientos_contables.id;


--
-- Name: bitacora_auditoria; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.bitacora_auditoria (
    id bigint NOT NULL,
    user_id bigint,
    modulo character varying(60) NOT NULL,
    accion character varying(80) NOT NULL,
    descripcion text NOT NULL,
    ip_address character varying(45),
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: bitacora_auditoria_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.bitacora_auditoria_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: bitacora_auditoria_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.bitacora_auditoria_id_seq OWNED BY public.bitacora_auditoria.id;


--
-- Name: cache; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration bigint NOT NULL
);


--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration bigint NOT NULL
);


--
-- Name: cajas_turnos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cajas_turnos (
    id bigint NOT NULL,
    codigo_turno character varying(30) NOT NULL,
    user_id bigint NOT NULL,
    fecha_apertura timestamp(0) without time zone NOT NULL,
    fecha_cierre timestamp(0) without time zone,
    monto_inicial numeric(12,2) NOT NULL,
    ventas_efectivo numeric(12,2) DEFAULT '0'::numeric NOT NULL,
    ventas_tarjeta numeric(12,2) DEFAULT '0'::numeric NOT NULL,
    ventas_transferencia numeric(12,2) DEFAULT '0'::numeric NOT NULL,
    monto_esperado numeric(12,2) DEFAULT '0'::numeric NOT NULL,
    monto_declarado numeric(12,2),
    diferencia numeric(12,2) DEFAULT '0'::numeric NOT NULL,
    estado character varying(20) DEFAULT 'Abierta'::character varying NOT NULL,
    observaciones text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: cajas_turnos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.cajas_turnos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: cajas_turnos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.cajas_turnos_id_seq OWNED BY public.cajas_turnos.id;


--
-- Name: clientes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.clientes (
    id bigint NOT NULL,
    nombre character varying(120) NOT NULL,
    identificacion character varying(40),
    telefono character varying(40),
    correo character varying(120),
    tipo character varying(40) DEFAULT 'Mostrador'::character varying NOT NULL,
    activo boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: clientes_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.clientes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: clientes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.clientes_id_seq OWNED BY public.clientes.id;


--
-- Name: controles_calidad; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.controles_calidad (
    id bigint NOT NULL,
    codigo_control character varying(30) NOT NULL,
    lote_id bigint NOT NULL,
    user_id bigint NOT NULL,
    fecha_inspeccion timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    unidades_inspeccionadas numeric(12,3) NOT NULL,
    unidades_no_conformes numeric(12,3) DEFAULT '0'::numeric NOT NULL,
    defecto text,
    disposicion character varying(20) NOT NULL,
    costo_perdido numeric(12,2) DEFAULT '0'::numeric NOT NULL,
    notas text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: controles_calidad_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.controles_calidad_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: controles_calidad_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.controles_calidad_id_seq OWNED BY public.controles_calidad.id;


--
-- Name: cuentas_contables; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cuentas_contables (
    id bigint NOT NULL,
    codigo character varying(20) NOT NULL,
    nombre character varying(120) NOT NULL,
    tipo character varying(30) NOT NULL,
    naturaleza character varying(20) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: cuentas_contables_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.cuentas_contables_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: cuentas_contables_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.cuentas_contables_id_seq OWNED BY public.cuentas_contables.id;


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection character varying(255) NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: finanzas_movimientos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.finanzas_movimientos (
    id bigint NOT NULL,
    folio character varying(30) NOT NULL,
    fecha date NOT NULL,
    tipo character varying(30) NOT NULL,
    categoria character varying(60) NOT NULL,
    concepto character varying(180) NOT NULL,
    referencia character varying(50),
    monto numeric(12,2) NOT NULL,
    estado character varying(25) DEFAULT 'Pagado'::character varying NOT NULL,
    fecha_vencimiento date,
    user_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: finanzas_movimientos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.finanzas_movimientos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: finanzas_movimientos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.finanzas_movimientos_id_seq OWNED BY public.finanzas_movimientos.id;


--
-- Name: folio_asientos_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.folio_asientos_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: folio_calidad_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.folio_calidad_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: folio_compras_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.folio_compras_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: folio_finanzas_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.folio_finanzas_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: folio_lotes_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.folio_lotes_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: folio_turnos_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.folio_turnos_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: folio_ventas_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.folio_ventas_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: insumos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.insumos (
    id bigint NOT NULL,
    codigo character varying(30) NOT NULL,
    nombre character varying(120) NOT NULL,
    categoria character varying(60) NOT NULL,
    unidad_id bigint NOT NULL,
    stock_actual numeric(12,3) DEFAULT '0'::numeric NOT NULL,
    stock_minimo numeric(12,3) DEFAULT '0'::numeric NOT NULL,
    costo_promedio numeric(12,2) DEFAULT '0'::numeric NOT NULL,
    activo boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: insumos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.insumos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: insumos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.insumos_id_seq OWNED BY public.insumos.id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


--
-- Name: jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: lecturas_temperatura; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.lecturas_temperatura (
    id bigint NOT NULL,
    dispositivo character varying(40) NOT NULL,
    temperatura numeric(5,2) NOT NULL,
    fuera_de_rango boolean DEFAULT false NOT NULL,
    origen character varying(20) NOT NULL,
    fecha_lectura timestamp(0) without time zone NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: lecturas_temperatura_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.lecturas_temperatura_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: lecturas_temperatura_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.lecturas_temperatura_id_seq OWNED BY public.lecturas_temperatura.id;


--
-- Name: lotes_produccion; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.lotes_produccion (
    id bigint NOT NULL,
    codigo_lote character varying(30) NOT NULL,
    receta_id bigint NOT NULL,
    producto_id bigint NOT NULL,
    user_id bigint NOT NULL,
    fecha_produccion date NOT NULL,
    cantidad_programada numeric(12,3) NOT NULL,
    cantidad_obtenida numeric(12,3),
    merma numeric(12,3) DEFAULT '0'::numeric NOT NULL,
    costo_total_lote numeric(12,2),
    costo_unitario_resultante numeric(12,2),
    estado character varying(30) DEFAULT 'Finalizado'::character varying NOT NULL,
    notas text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    estado_calidad character varying(30) DEFAULT 'Pendiente'::character varying NOT NULL
);


--
-- Name: lotes_produccion_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.lotes_produccion_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: lotes_produccion_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.lotes_produccion_id_seq OWNED BY public.lotes_produccion.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: modulos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.modulos (
    id bigint NOT NULL,
    clave character varying(40) NOT NULL,
    nombre character varying(80) NOT NULL,
    ruta character varying(80) NOT NULL,
    seccion character varying(40) DEFAULT 'Operaciones'::character varying NOT NULL,
    orden integer DEFAULT 1 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: modulos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.modulos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: modulos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.modulos_id_seq OWNED BY public.modulos.id;


--
-- Name: movimientos_inventario; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.movimientos_inventario (
    id bigint NOT NULL,
    fecha timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    tipo_item character varying(20) NOT NULL,
    item_id bigint NOT NULL,
    nombre_item character varying(140) NOT NULL,
    tipo_movimiento character varying(50) NOT NULL,
    referencia character varying(50) NOT NULL,
    cantidad numeric(12,3) NOT NULL,
    stock_resultante numeric(12,3) NOT NULL,
    costo_unitario numeric(12,2) DEFAULT '0'::numeric NOT NULL,
    user_id bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: movimientos_inventario_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.movimientos_inventario_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: movimientos_inventario_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.movimientos_inventario_id_seq OWNED BY public.movimientos_inventario.id;


--
-- Name: orden_compra_lineas; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.orden_compra_lineas (
    id bigint NOT NULL,
    orden_compra_id bigint NOT NULL,
    insumo_id bigint NOT NULL,
    cantidad numeric(12,3) NOT NULL,
    costo_unitario numeric(12,2) NOT NULL,
    subtotal numeric(12,2) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: orden_compra_lineas_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.orden_compra_lineas_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: orden_compra_lineas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.orden_compra_lineas_id_seq OWNED BY public.orden_compra_lineas.id;


--
-- Name: ordenes_compra; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.ordenes_compra (
    id bigint NOT NULL,
    folio character varying(30) NOT NULL,
    proveedor_id bigint NOT NULL,
    user_id bigint NOT NULL,
    fecha_emision date NOT NULL,
    fecha_recepcion date,
    estado character varying(30) DEFAULT 'Recibida'::character varying NOT NULL,
    subtotal numeric(12,2) NOT NULL,
    impuestos numeric(12,2) DEFAULT '0'::numeric NOT NULL,
    total numeric(12,2) NOT NULL,
    notas text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: ordenes_compra_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.ordenes_compra_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: ordenes_compra_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.ordenes_compra_id_seq OWNED BY public.ordenes_compra.id;


--
-- Name: parametros; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.parametros (
    clave character varying(60) NOT NULL,
    valor character varying(120) NOT NULL,
    descripcion character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: partidas_asiento; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.partidas_asiento (
    id bigint NOT NULL,
    asiento_contable_id bigint NOT NULL,
    cuenta_contable_id bigint NOT NULL,
    descripcion character varying(180),
    debe numeric(12,2) DEFAULT '0'::numeric NOT NULL,
    haber numeric(12,2) DEFAULT '0'::numeric NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: partidas_asiento_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.partidas_asiento_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: partidas_asiento_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.partidas_asiento_id_seq OWNED BY public.partidas_asiento.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


--
-- Name: productos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.productos (
    id bigint NOT NULL,
    sku character varying(30) NOT NULL,
    nombre character varying(120) NOT NULL,
    sabor character varying(60) NOT NULL,
    presentacion character varying(60) NOT NULL,
    precio_venta numeric(12,2) NOT NULL,
    costo_unitario numeric(12,2) DEFAULT '0'::numeric NOT NULL,
    stock_actual numeric(12,3) DEFAULT '0'::numeric NOT NULL,
    stock_minimo numeric(12,3) DEFAULT '0'::numeric NOT NULL,
    activo boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: productos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.productos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: productos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.productos_id_seq OWNED BY public.productos.id;


--
-- Name: proveedores; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.proveedores (
    id bigint NOT NULL,
    nombre character varying(120) NOT NULL,
    ruc_nit character varying(40) NOT NULL,
    contacto character varying(100) NOT NULL,
    telefono character varying(40) NOT NULL,
    correo character varying(120),
    direccion character varying(200),
    activo boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: proveedores_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.proveedores_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: proveedores_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.proveedores_id_seq OWNED BY public.proveedores.id;


--
-- Name: receta_insumos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.receta_insumos (
    id bigint NOT NULL,
    receta_id bigint NOT NULL,
    insumo_id bigint NOT NULL,
    cantidad_requerida numeric(12,3) NOT NULL,
    merma_estimada_porcentaje numeric(5,2) DEFAULT '0'::numeric NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: receta_insumos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.receta_insumos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: receta_insumos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.receta_insumos_id_seq OWNED BY public.receta_insumos.id;


--
-- Name: recetas; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.recetas (
    id bigint NOT NULL,
    producto_id bigint NOT NULL,
    nombre character varying(140) NOT NULL,
    rendimiento_unidades numeric(12,3) NOT NULL,
    tiempo_preparacion_min integer DEFAULT 45 NOT NULL,
    instrucciones text,
    activa boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: recetas_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.recetas_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: recetas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.recetas_id_seq OWNED BY public.recetas.id;


--
-- Name: rol_modulo; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.rol_modulo (
    rol_id bigint NOT NULL,
    modulo_id bigint NOT NULL
);


--
-- Name: roles; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.roles (
    id bigint NOT NULL,
    nombre character varying(40) NOT NULL,
    descripcion character varying(180),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: roles_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.roles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.roles_id_seq OWNED BY public.roles.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


--
-- Name: unidades_medida; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.unidades_medida (
    id bigint NOT NULL,
    nombre character varying(60) NOT NULL,
    abreviatura character varying(15) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: unidades_medida_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.unidades_medida_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: unidades_medida_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.unidades_medida_id_seq OWNED BY public.unidades_medida.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    rol character varying(40) DEFAULT 'Administrador'::character varying NOT NULL,
    activo boolean DEFAULT true NOT NULL,
    ultimo_acceso timestamp(0) without time zone,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    rol_id bigint
);


--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: venta_lineas; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.venta_lineas (
    id bigint NOT NULL,
    venta_id bigint NOT NULL,
    producto_id bigint NOT NULL,
    cantidad numeric(12,3) NOT NULL,
    precio_unitario numeric(12,2) NOT NULL,
    costo_unitario numeric(12,2) NOT NULL,
    subtotal numeric(12,2) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: venta_lineas_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.venta_lineas_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: venta_lineas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.venta_lineas_id_seq OWNED BY public.venta_lineas.id;


--
-- Name: ventas; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.ventas (
    id bigint NOT NULL,
    folio character varying(30) NOT NULL,
    caja_turno_id bigint NOT NULL,
    cliente_id bigint,
    user_id bigint NOT NULL,
    fecha_venta timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    metodo_pago character varying(30) NOT NULL,
    subtotal numeric(12,2) NOT NULL,
    impuestos numeric(12,2) DEFAULT '0'::numeric NOT NULL,
    total numeric(12,2) NOT NULL,
    estado character varying(25) DEFAULT 'Completada'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: ventas_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.ventas_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: ventas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.ventas_id_seq OWNED BY public.ventas.id;


--
-- Name: asientos_contables id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asientos_contables ALTER COLUMN id SET DEFAULT nextval('public.asientos_contables_id_seq'::regclass);


--
-- Name: bitacora_auditoria id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bitacora_auditoria ALTER COLUMN id SET DEFAULT nextval('public.bitacora_auditoria_id_seq'::regclass);


--
-- Name: cajas_turnos id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cajas_turnos ALTER COLUMN id SET DEFAULT nextval('public.cajas_turnos_id_seq'::regclass);


--
-- Name: clientes id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.clientes ALTER COLUMN id SET DEFAULT nextval('public.clientes_id_seq'::regclass);


--
-- Name: controles_calidad id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.controles_calidad ALTER COLUMN id SET DEFAULT nextval('public.controles_calidad_id_seq'::regclass);


--
-- Name: cuentas_contables id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cuentas_contables ALTER COLUMN id SET DEFAULT nextval('public.cuentas_contables_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: finanzas_movimientos id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.finanzas_movimientos ALTER COLUMN id SET DEFAULT nextval('public.finanzas_movimientos_id_seq'::regclass);


--
-- Name: insumos id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.insumos ALTER COLUMN id SET DEFAULT nextval('public.insumos_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: lecturas_temperatura id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.lecturas_temperatura ALTER COLUMN id SET DEFAULT nextval('public.lecturas_temperatura_id_seq'::regclass);


--
-- Name: lotes_produccion id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.lotes_produccion ALTER COLUMN id SET DEFAULT nextval('public.lotes_produccion_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: modulos id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.modulos ALTER COLUMN id SET DEFAULT nextval('public.modulos_id_seq'::regclass);


--
-- Name: movimientos_inventario id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.movimientos_inventario ALTER COLUMN id SET DEFAULT nextval('public.movimientos_inventario_id_seq'::regclass);


--
-- Name: orden_compra_lineas id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.orden_compra_lineas ALTER COLUMN id SET DEFAULT nextval('public.orden_compra_lineas_id_seq'::regclass);


--
-- Name: ordenes_compra id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.ordenes_compra ALTER COLUMN id SET DEFAULT nextval('public.ordenes_compra_id_seq'::regclass);


--
-- Name: partidas_asiento id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.partidas_asiento ALTER COLUMN id SET DEFAULT nextval('public.partidas_asiento_id_seq'::regclass);


--
-- Name: productos id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.productos ALTER COLUMN id SET DEFAULT nextval('public.productos_id_seq'::regclass);


--
-- Name: proveedores id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.proveedores ALTER COLUMN id SET DEFAULT nextval('public.proveedores_id_seq'::regclass);


--
-- Name: receta_insumos id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.receta_insumos ALTER COLUMN id SET DEFAULT nextval('public.receta_insumos_id_seq'::regclass);


--
-- Name: recetas id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.recetas ALTER COLUMN id SET DEFAULT nextval('public.recetas_id_seq'::regclass);


--
-- Name: roles id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles ALTER COLUMN id SET DEFAULT nextval('public.roles_id_seq'::regclass);


--
-- Name: unidades_medida id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.unidades_medida ALTER COLUMN id SET DEFAULT nextval('public.unidades_medida_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: venta_lineas id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.venta_lineas ALTER COLUMN id SET DEFAULT nextval('public.venta_lineas_id_seq'::regclass);


--
-- Name: ventas id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.ventas ALTER COLUMN id SET DEFAULT nextval('public.ventas_id_seq'::regclass);


--
-- Data for Name: asientos_contables; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.asientos_contables (id, numero_asiento, fecha, modulo_origen, referencia, concepto, total_debe, total_haber, user_id, created_at, updated_at) FROM stdin;
1	ASI-2026-0174	2026-10-30	Contabilidad	APE-2026	Asiento de apertura de operaciones y capital social inicial	80900.00	80900.00	1	2026-10-30 15:05:18	2026-10-30 15:05:18
2	ASI-2026-0175	2026-10-30	Compras	OC-2026-0001	Recepción de orden de compra OC-2026-0001 (Pagado)	8160.00	8160.00	1	2026-10-30 15:05:18	2026-10-30 15:05:18
3	ASI-2026-0176	2026-10-30	Producción	LOT-2026-001	Transformación de insumos a producto terminado (LOT-2026-001 - Gelato Fresa Imperial)	2340.00	2340.00	2	2026-10-30 15:05:18	2026-10-30 15:05:18
4	ASI-2026-0177	2026-10-30	Producción	LOT-2026-002	Transformación de insumos a producto terminado (LOT-2026-002 - Gelato Pistacho Siciliano)	2400.00	2400.00	2	2026-10-30 15:05:18	2026-10-30 15:05:18
5	ASI-2026-0178	2026-10-30	Ventas	VTA-2026-0001	Póliza de venta VTA-2026-0001 (Efectivo)	1305.00	1305.00	3	2026-10-30 15:05:18	2026-10-30 15:05:18
6	ASI-2026-0179	2026-10-30	Ventas	VTA-2026-0002	Póliza de venta VTA-2026-0002 (Transferencia)	10560.00	10560.00	3	2026-10-30 15:05:18	2026-10-30 15:05:18
\.


--
-- Data for Name: bitacora_auditoria; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.bitacora_auditoria (id, user_id, modulo, accion, descripcion, ip_address, created_at) FROM stdin;
1	1	Seguridad	Inicialización de Sistema	Configuración de parámetros maestros y roles operativos de Gelawitto ERP.	127.0.0.1	2026-10-27 15:05:18
2	2	Producción	Cierre de Lote	Lote LOT-2026-001 finalizado con 19.500 L de Gelato Fresa Imperial.	127.0.0.1	2026-10-29 15:05:18
3	3	Ventas y Caja	Apertura de Turno	Apertura de turno TUR-2026-001 con fondo inicial de C$ 3,500.00.	127.0.0.1	2026-10-30 09:05:18
\.


--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.cache (key, value, expiration) FROM stdin;
\.


--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- Data for Name: cajas_turnos; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.cajas_turnos (id, codigo_turno, user_id, fecha_apertura, fecha_cierre, monto_inicial, ventas_efectivo, ventas_tarjeta, ventas_transferencia, monto_esperado, monto_declarado, diferencia, estado, observaciones, created_at, updated_at) FROM stdin;
1	TUR-2026-001	3	2026-10-30 09:05:18	\N	3500.00	3680.00	4925.00	0.00	7180.00	\N	0.00	Abierta	Turno matutino vitrina principal.	2026-10-30 09:05:18	2026-10-30 14:05:18
\.


--
-- Data for Name: clientes; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.clientes (id, nombre, identificacion, telefono, correo, tipo, activo, created_at, updated_at) FROM stdin;
1	Cliente de Mostrador	CF-0001	—	mostrador@gelawitto.com	Mostrador	t	2026-10-30 15:05:18	2026-10-30 15:05:18
2	Cafetería y Pastelería El Mirador	J031000077210	+505 2278 4012	compras@cafeteriaelmirador.com	Mayorista	t	2026-10-30 15:05:18	2026-10-30 15:05:18
\.


--
-- Data for Name: controles_calidad; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.controles_calidad (id, codigo_control, lote_id, user_id, fecha_inspeccion, unidades_inspeccionadas, unidades_no_conformes, defecto, disposicion, costo_perdido, notas, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: cuentas_contables; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.cuentas_contables (id, codigo, nombre, tipo, naturaleza, created_at, updated_at) FROM stdin;
1	1101	Caja y Bancos (Efectivo y Equivalentes)	Activo	Deudora	2026-10-30 15:05:17	2026-10-30 15:05:17
2	1102	Cuentas por Cobrar Clientes	Activo	Deudora	2026-10-30 15:05:17	2026-10-30 15:05:17
3	1103	IVA Acreditable (15%)	Activo	Deudora	2026-10-30 15:05:17	2026-10-30 15:05:17
4	1104	Inventario de Materia Prima e Insumos	Activo	Deudora	2026-10-30 15:05:17	2026-10-30 15:05:17
5	1105	Inventario de Producto Terminado (Helados)	Activo	Deudora	2026-10-30 15:05:17	2026-10-30 15:05:17
6	2101	Cuentas por Pagar Proveedores	Pasivo	Acreedora	2026-10-30 15:05:17	2026-10-30 15:05:17
7	2102	IVA por Pagar (15%)	Pasivo	Acreedora	2026-10-30 15:05:17	2026-10-30 15:05:17
8	3101	Capital Social y Patrimonio Inicial	Patrimonio	Acreedora	2026-10-30 15:05:17	2026-10-30 15:05:17
9	4101	Ingresos por Ventas de Helados	Ingreso	Acreedora	2026-10-30 15:05:17	2026-10-30 15:05:17
10	5101	Costo de Ventas de Producción	Costo	Deudora	2026-10-30 15:05:17	2026-10-30 15:05:17
11	5201	Gastos Operativos de Planta	Gasto	Deudora	2026-10-30 15:05:17	2026-10-30 15:05:17
12	5202	Pérdidas por Mermas y Scrap de Calidad	Gasto	Deudora	2026-10-30 15:05:17	2026-10-30 15:05:17
\.


--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- Data for Name: finanzas_movimientos; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.finanzas_movimientos (id, folio, fecha, tipo, categoria, concepto, referencia, monto, estado, fecha_vencimiento, user_id, created_at, updated_at) FROM stdin;
1	FIN-2026-001	2026-10-27	Egreso	Compra de Insumos	Pago factura Lácteos del Valle S.A. (OC-2026-0001)	OC-2026-0001	9044.38	Pagado	\N	1	2026-10-27 15:05:18	2026-10-27 15:05:18
2	FIN-2026-002	2026-10-28	Cuenta por Pagar	Proveedores	Factura a crédito 15 días Frutas Selectas (OC-2026-0002)	OC-2026-0002	6711.68	Pendiente	2026-11-12	1	2026-10-28 15:05:18	2026-10-28 15:05:18
3	FIN-2026-003	2026-10-30	Ingreso	Ventas Mostrador y Mayorista	Recaudación acumulada turno TUR-2026-001	TUR-2026-001	8605.00	Pagado	\N	3	2026-10-30 15:05:18	2026-10-30 15:05:18
\.


--
-- Data for Name: insumos; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.insumos (id, codigo, nombre, categoria, unidad_id, stock_actual, stock_minimo, costo_promedio, activo, created_at, updated_at) FROM stdin;
1	INS-001	Leche entera fresca pasteurizada	Lácteos	2	85.000	30.000	48.00	t	2026-10-30 15:05:18	2026-10-30 15:05:18
2	INS-002	Crema de leche batible 35% grasa	Lácteos	2	28.500	15.000	175.00	t	2026-10-30 15:05:18	2026-10-30 15:05:18
3	INS-003	Fresa orgánica seleccionada	Frutas	1	18.000	12.000	140.00	t	2026-10-30 15:05:18	2026-10-30 15:05:18
4	INS-004	Frambuesa silvestre congelada IQF	Frutas	1	6.500	10.000	235.00	t	2026-10-30 15:05:18	2026-10-30 15:05:18
5	INS-005	Mora de altura seleccionada	Frutas	1	14.000	10.000	125.00	t	2026-10-30 15:05:18	2026-10-30 15:05:18
6	INS-006	Pulpa natural de durazno criollo	Frutas	1	8.000	10.000	155.00	t	2026-10-30 15:05:18	2026-10-30 15:05:18
7	INS-007	Azúcar blanca cristalina y dextrosa	Endulzantes	1	64.000	25.000	40.00	t	2026-10-30 15:05:18	2026-10-30 15:05:18
8	INS-008	Envase isotérmico biodegradable 1 Litro	Empaques	3	140.000	50.000	24.00	t	2026-10-30 15:05:18	2026-10-30 15:05:18
9	INS-009	Gelatina en polvo Great Value 40 g	Gelatinas	1	0.000	0.000	1125.00	t	2026-10-30 15:05:18	2026-10-30 15:05:18
10	INS-010	Agua potable (250 mL caliente + 250 mL fría)	Agua	2	0.000	0.000	1.00	t	2026-10-30 15:05:18	2026-10-30 15:05:18
11	INS-011	Frutas variadas (cantidad pendiente de estandarizar)	Frutas	1	0.000	0.000	150.00	t	2026-10-30 15:05:18	2026-10-30 15:05:18
12	INS-012	Vaso reciclable 100 mL con tapa	Empaques	3	0.000	0.000	8.00	t	2026-10-30 15:05:18	2026-10-30 15:05:18
\.


--
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) FROM stdin;
\.


--
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.jobs (id, queue, payload, attempts, reserved_at, available_at, created_at) FROM stdin;
\.


--
-- Data for Name: lecturas_temperatura; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.lecturas_temperatura (id, dispositivo, temperatura, fuera_de_rango, origen, fecha_lectura, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: lotes_produccion; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.lotes_produccion (id, codigo_lote, receta_id, producto_id, user_id, fecha_produccion, cantidad_programada, cantidad_obtenida, merma, costo_total_lote, costo_unitario_resultante, estado, notas, created_at, updated_at, estado_calidad) FROM stdin;
1	LOT-2026-001	1	1	2	2026-10-29	20.000	19.500	0.500	3802.50	195.00	Finalizado	Textura sedosa óptima, overrun del 28%.	2026-10-29 15:05:18	2026-10-29 15:05:18	Liberado
2	LOT-2026-002	2	2	2	2026-10-30	10.000	9.800	0.200	2205.00	225.00	Finalizado	Acidez balanceada, lote destinado a vitrina principal.	2026-10-30 10:05:18	2026-10-30 10:05:18	Liberado
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	0001_01_01_000000_create_users_table	1
2	0001_01_01_000001_create_cache_table	1
3	0001_01_01_000002_create_jobs_table	1
4	0001_01_02_000000_create_erp_tables	1
5	2026_10_21_000000_permitir_resultados_pendientes_en_lotes_produccion	1
6	2026_10_21_000100_crear_controles_calidad	1
7	2026_10_21_000200_crear_lecturas_temperatura	1
8	2026_10_28_000100_crear_tablas_contabilidad	1
9	2026_10_29_000100_crear_parametros_roles_y_secuencias	1
\.


--
-- Data for Name: modulos; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.modulos (id, clave, nombre, ruta, seccion, orden, created_at, updated_at) FROM stdin;
1	dashboard	Tablero Ejecutivo	/dashboard	Principal	1	2026-10-30 15:05:17	2026-10-30 15:05:17
2	catalogos	Catálogos Maestros	/catalogos	Operaciones	2	2026-10-30 15:05:17	2026-10-30 15:05:17
3	compras	Compras a Proveedores	/compras	Comercial	3	2026-10-30 15:05:17	2026-10-30 15:05:17
4	inventario	Inventario y Kardex	/inventario	Operaciones	4	2026-10-30 15:05:17	2026-10-30 15:05:17
5	produccion	Recetas y Producción	/produccion	Operaciones	5	2026-10-30 15:05:17	2026-10-30 15:05:17
6	calidad	Calidad y Trazabilidad	/calidad	Operaciones	6	2026-10-30 15:05:17	2026-10-30 15:05:17
7	ventas	Ventas y Turno de Caja	/ventas	Comercial	7	2026-10-30 15:05:17	2026-10-30 15:05:17
8	finanzas	Finanzas y Cuentas	/finanzas	Finanzas	8	2026-10-30 15:05:17	2026-10-30 15:05:17
9	seguridad	Usuarios y Auditoría	/seguridad	Seguridad	9	2026-10-30 15:05:17	2026-10-30 15:05:17
\.


--
-- Data for Name: movimientos_inventario; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.movimientos_inventario (id, fecha, tipo_item, item_id, nombre_item, tipo_movimiento, referencia, cantidad, stock_resultante, costo_unitario, user_id, created_at, updated_at) FROM stdin;
1	2026-10-27 15:05:18	insumo	1	Leche entera fresca pasteurizada	Entrada por Compra	OC-2026-0001	95.000	95.000	48.00	1	2026-10-27 15:05:18	2026-10-27 15:05:18
2	2026-10-29 15:05:18	insumo	1	Leche entera fresca pasteurizada	Consumo de Producción	LOT-2026-001	-10.000	85.000	48.00	2	2026-10-29 15:05:18	2026-10-29 15:05:18
3	2026-10-29 15:05:18	producto	1	Gelato Artesanal Fresa Imperial	Ingreso de Producción	LOT-2026-001	19.500	28.000	195.00	2	2026-10-29 15:05:18	2026-10-29 15:05:18
4	2026-10-30 12:05:18	producto	1	Gelato Artesanal Fresa Imperial	Salida por Venta	VTA-2026-0001	-4.000	24.000	195.00	3	2026-10-30 12:05:18	2026-10-30 12:05:18
\.


--
-- Data for Name: orden_compra_lineas; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.orden_compra_lineas (id, orden_compra_id, insumo_id, cantidad, costo_unitario, subtotal, created_at, updated_at) FROM stdin;
1	1	1	95.000	48.00	4560.00	2026-10-27 15:05:18	2026-10-27 15:05:18
2	1	2	25.625	175.00	4484.38	2026-10-27 15:05:18	2026-10-27 15:05:18
3	2	3	30.000	140.00	4200.00	2026-10-28 15:05:18	2026-10-28 15:05:18
4	2	4	10.688	235.00	2511.68	2026-10-28 15:05:18	2026-10-28 15:05:18
\.


--
-- Data for Name: ordenes_compra; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.ordenes_compra (id, folio, proveedor_id, user_id, fecha_emision, fecha_recepcion, estado, subtotal, impuestos, total, notas, created_at, updated_at) FROM stdin;
1	OC-2026-0001	1	1	2026-10-27	2026-10-27	Recibida	9044.38	0.00	9044.38	Entrega refrigerada a 3 °C verificada en recepción.	2026-10-27 15:05:18	2026-10-27 15:05:18
2	OC-2026-0002	2	1	2026-10-28	2026-10-28	Recibida	6711.68	0.00	6711.68	Cosecha fresca de altura.	2026-10-28 15:05:18	2026-10-28 15:05:18
\.


--
-- Data for Name: parametros; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.parametros (clave, valor, descripcion, created_at, updated_at) FROM stdin;
iva_porcentaje	15	Porcentaje de Impuesto al Valor Agregado (IVA) aplicable en compras y ventas	2026-10-30 15:05:17	2026-10-30 15:05:17
refrigerador_codigo	REF-01	Código del refrigerador industrial de maduración y enfriamiento de lotes	2026-10-30 15:05:17	2026-10-30 15:05:17
refrigerador_temp_min	2.0	Temperatura mínima aceptable en grados Celsius para el refrigerador de maduración	2026-10-30 15:05:17	2026-10-30 15:05:17
refrigerador_temp_max	6.0	Temperatura máxima aceptable en grados Celsius para el refrigerador de maduración	2026-10-30 15:05:17	2026-10-30 15:05:17
\.


--
-- Data for Name: partidas_asiento; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.partidas_asiento (id, asiento_contable_id, cuenta_contable_id, descripcion, debe, haber, created_at, updated_at) FROM stdin;
1	1	1	Fondo inicial en caja y bancos	50000.00	0.00	2026-10-30 15:05:18	2026-10-30 15:05:18
2	1	4	Inventario inicial de materia prima	18500.00	0.00	2026-10-30 15:05:18	2026-10-30 15:05:18
3	1	5	Inventario inicial de producto terminado	12400.00	0.00	2026-10-30 15:05:18	2026-10-30 15:05:18
4	1	8	Aportación patrimonial de socios	0.00	80900.00	2026-10-30 15:05:18	2026-10-30 15:05:18
5	2	4	Ingreso a almacén de materia prima (OC-2026-0001)	8160.00	0.00	2026-10-30 15:05:18	2026-10-30 15:05:18
6	2	1	Pago de contado OC OC-2026-0001	0.00	8160.00	2026-10-30 15:05:18	2026-10-30 15:05:18
7	3	5	Alta de producto terminado Gelato Fresa Imperial	2340.00	0.00	2026-10-30 15:05:18	2026-10-30 15:05:18
8	3	4	Consumo de materia prima en lote LOT-2026-001	0.00	2340.00	2026-10-30 15:05:18	2026-10-30 15:05:18
9	4	5	Alta de producto terminado Gelato Pistacho Siciliano	2400.00	0.00	2026-10-30 15:05:18	2026-10-30 15:05:18
10	4	4	Consumo de materia prima en lote LOT-2026-002	0.00	2400.00	2026-10-30 15:05:18	2026-10-30 15:05:18
11	5	1	Cobro en Efectivo (VTA-2026-0001)	945.00	0.00	2026-10-30 15:05:18	2026-10-30 15:05:18
12	5	9	Ingreso por venta VTA-2026-0001	0.00	945.00	2026-10-30 15:05:18	2026-10-30 15:05:18
13	5	10	Reconocimiento de costo de lo vendido (VTA-2026-0001)	360.00	0.00	2026-10-30 15:05:18	2026-10-30 15:05:18
14	5	5	Descargo de inventario de helados (VTA-2026-0001)	0.00	360.00	2026-10-30 15:05:18	2026-10-30 15:05:18
15	6	1	Cobro en Transferencia (VTA-2026-0002)	7660.00	0.00	2026-10-30 15:05:18	2026-10-30 15:05:18
16	6	9	Ingreso por venta VTA-2026-0002	0.00	7660.00	2026-10-30 15:05:18	2026-10-30 15:05:18
17	6	10	Reconocimiento de costo de lo vendido (VTA-2026-0002)	2900.00	0.00	2026-10-30 15:05:18	2026-10-30 15:05:18
18	6	5	Descargo de inventario de helados (VTA-2026-0002)	0.00	2900.00	2026-10-30 15:05:18	2026-10-30 15:05:18
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- Data for Name: productos; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.productos (id, sku, nombre, sabor, presentacion, precio_venta, costo_unitario, stock_actual, stock_minimo, activo, created_at, updated_at) FROM stdin;
1	GEL-FRE-1L	Gelato Artesanal Fresa Imperial	Fresa	Tina 1 Litro	530.00	195.00	24.000	8.000	t	2026-10-30 15:05:18	2026-10-30 15:05:18
2	GEL-FRA-1L	Gelato Frambuesa Silvestre	Frambuesa	Tina 1 Litro	580.00	225.00	16.000	8.000	t	2026-10-30 15:05:18	2026-10-30 15:05:18
3	GEL-MOR-1L	Sorbete Artesanal de Mora Intensa	Mora	Tina 1 Litro	500.00	180.00	19.000	6.000	t	2026-10-30 15:05:18	2026-10-30 15:05:18
4	GEL-DUR-1L	Gelato Cremoso de Durazno Asado	Durazno	Tina 1 Litro	550.00	205.00	11.000	8.000	t	2026-10-30 15:05:18	2026-10-30 15:05:18
5	GEL-NAT-1L	Gelato Fior di Nata Clásico	Nata	Tina 1 Litro	490.00	172.00	22.000	10.000	t	2026-10-30 15:05:18	2026-10-30 15:05:18
6	GLW-TRA-100	Gelawitto tradicional	Fresa y limón	Vaso 100 mL con tapa	100.00	17.10	0.000	0.000	t	2026-10-30 15:05:18	2026-10-30 15:05:18
7	GLW-FRU-100	Gelawitto con frutas variadas	Frutas variadas	Vaso 100 mL con tapa	100.00	17.10	0.000	0.000	t	2026-10-30 15:05:18	2026-10-30 15:05:18
\.


--
-- Data for Name: proveedores; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.proveedores (id, nombre, ruc_nit, contacto, telefono, correo, direccion, activo, created_at, updated_at) FROM stdin;
1	Lácteos del Valle S.A.	J031000045129	Ing. Roberto Solís	+505 8842 1920	pedidos@lacteosdelvalle.com	Km 14 Carretera Norte, Parque Industrial	t	2026-10-30 15:05:18	2026-10-30 15:05:18
2	Frutas Selectas de la Montaña	J031000089312	María Elena Chavarría	+505 8715 4401	ventas@frutasselectas.com	 Valle de Sébaco, Bodega Central 4	t	2026-10-30 15:05:18	2026-10-30 15:05:18
3	Empaques Térmicos BioPack	J031000091204	Lic. Fernando Rivas	+505 8920 3310	comercial@biopack.com	Pista Suburbana, Módulo 12B	t	2026-10-30 15:05:18	2026-10-30 15:05:18
\.


--
-- Data for Name: receta_insumos; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.receta_insumos (id, receta_id, insumo_id, cantidad_requerida, merma_estimada_porcentaje, created_at, updated_at) FROM stdin;
1	1	1	5.000	2.00	2026-10-30 15:05:18	2026-10-30 15:05:18
2	1	2	2.500	1.50	2026-10-30 15:05:18	2026-10-30 15:05:18
3	1	3	3.200	4.00	2026-10-30 15:05:18	2026-10-30 15:05:18
4	1	7	1.600	0.50	2026-10-30 15:05:18	2026-10-30 15:05:18
5	1	8	10.000	0.00	2026-10-30 15:05:18	2026-10-30 15:05:18
6	2	1	4.800	2.00	2026-10-30 15:05:18	2026-10-30 15:05:18
7	2	2	2.500	1.50	2026-10-30 15:05:18	2026-10-30 15:05:18
8	2	4	3.500	5.00	2026-10-30 15:05:18	2026-10-30 15:05:18
9	2	7	1.700	0.50	2026-10-30 15:05:18	2026-10-30 15:05:18
10	2	8	10.000	0.00	2026-10-30 15:05:18	2026-10-30 15:05:18
11	3	9	0.040	0.00	2026-10-30 15:05:18	2026-10-30 15:05:18
12	3	10	0.500	0.00	2026-10-30 15:05:18	2026-10-30 15:05:18
13	3	12	5.000	0.00	2026-10-30 15:05:18	2026-10-30 15:05:18
14	4	9	0.040	0.00	2026-10-30 15:05:18	2026-10-30 15:05:18
15	4	10	0.500	0.00	2026-10-30 15:05:18	2026-10-30 15:05:18
16	4	12	5.000	0.00	2026-10-30 15:05:18	2026-10-30 15:05:18
\.


--
-- Data for Name: recetas; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.recetas (id, producto_id, nombre, rendimiento_unidades, tiempo_preparacion_min, instrucciones, activa, created_at, updated_at) FROM stdin;
1	1	Fórmula Maestra Fresa Imperial (Base 10 Litros)	10.000	50	Pasteurizar base láctea a 85 °C, madurar 6 horas a 4 °C, incorporar fruta fresca triturada y mantecar a -9 °C.	t	2026-10-30 15:05:18	2026-10-30 15:05:18
2	2	Fórmula Maestra Frambuesa Silvestre (Base 10 Litros)	10.000	55	Infusionar base cremosa en frío, tamizar semillas al 60% y mantecar a baja revolución.	t	2026-10-30 15:05:18	2026-10-30 15:05:18
3	6	Receta base Gelawitto tradicional (lote de 5)	5.000	45	1. Verificar disponibilidad de materiales.\n2. Medir 250 mL de agua caliente y 250 mL de agua fría.\n3. Contar 5 vasos reciclables con tapa.\n4. Disolver 40 g de gelatina en el agua caliente.\n5. Mezclar hasta eliminar grumos.\n6. Agregar el agua fría y homogeneizar.\n7. Dosificar la mezcla en los vasos.\n8. Colocar las tapas.\n9. Refrigerar hasta solidificar (tiempo pendiente de validar).\n10. Inspeccionar la calidad del producto.\n11. Etiquetar, registrar el lote terminado y almacenar en refrigeración.	t	2026-10-30 15:05:18	2026-10-30 15:05:18
4	7	Receta base Gelawitto con frutas (lote de 5)	5.000	45	1. Verificar disponibilidad de materiales.\n2. Medir 250 mL de agua caliente y 250 mL de agua fría.\n3. Contar 5 vasos reciclables con tapa.\n4. Disolver 40 g de gelatina en el agua caliente.\n5. Mezclar hasta eliminar grumos.\n6. Agregar el agua fría y homogeneizar la mezcla.\n7. Preparar las frutas variadas (cantidad por vaso pendiente de estandarizar).\n8. Dosificar la fruta en cada vaso.\n9. Dosificar la mezcla en los vasos hasta completar aproximadamente 100 mL.\n10. Colocar las tapas.\n11. Refrigerar hasta solidificar (tiempo pendiente de validar).\n12. Inspeccionar la calidad del producto.\n13. Etiquetar, registrar el lote terminado y almacenar en refrigeración.	t	2026-10-30 15:05:18	2026-10-30 15:05:18
\.


--
-- Data for Name: rol_modulo; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.rol_modulo (rol_id, modulo_id) FROM stdin;
1	1
1	2
1	3
1	4
1	5
1	6
1	7
1	8
1	9
2	1
2	2
2	4
2	5
2	6
3	1
3	4
3	7
4	1
4	2
4	3
4	4
4	8
\.


--
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.roles (id, nombre, descripcion, created_at, updated_at) FROM stdin;
1	Administrador	Control total del sistema, seguridad, usuarios y auditoría	2026-10-30 15:05:17	2026-10-30 15:05:17
2	Producción	Formulación técnica, simulador MRP, lotes y control de calidad	2026-10-30 15:05:17	2026-10-30 15:05:17
3	Cajero	Apertura de turno, punto de venta y arqueo de caja	2026-10-30 15:05:17	2026-10-30 15:05:17
4	Contador	Compras, libro diario de partida doble y estados financieros	2026-10-30 15:05:17	2026-10-30 15:05:17
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
\.


--
-- Data for Name: unidades_medida; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.unidades_medida (id, nombre, abreviatura, created_at, updated_at) FROM stdin;
1	Kilogramo	kg	2026-10-30 15:05:18	2026-10-30 15:05:18
2	Litro	L	2026-10-30 15:05:18	2026-10-30 15:05:18
3	Unidad	ud	2026-10-30 15:05:18	2026-10-30 15:05:18
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.users (id, name, email, email_verified_at, password, rol, activo, ultimo_acceso, remember_token, created_at, updated_at, rol_id) FROM stdin;
1	Eduardo Cantarero	admin@gelawitto.com	\N	$2y$12$XAMo1X1dhZj1.LeHUV1t5e41vbHOwBgDtybLBpVxrFpFQM3XWbd0W	Administrador	t	2026-10-30 14:53:17	\N	2026-10-30 15:05:17	2026-10-30 15:05:17	1
2	Moises Zelaya	produccion@gelawitto.com	\N	$2y$12$s.YRMsm7aXdxfwdjNRcADO/8G5MBivcHHNEFCSeElVO11qodILn0i	Producción	t	2026-10-30 13:05:17	\N	2026-10-30 15:05:17	2026-10-30 15:05:17	2
3	Valeria Mendoza	caja@gelawitto.com	\N	$2y$12$D1MfmiCSnFsjfLTigAAGruyts6SowYW8UgXbkxpdHGlnOUHNA6yci	Cajero	t	2026-10-30 14:30:18	\N	2026-10-30 15:05:18	2026-10-30 15:05:18	3
4	Carlos Navarrete	finanzas@gelawitto.com	\N	$2y$12$2AY3dm8fbYJcODjCjm4pn.UfMppgRj/gVICY8PiGTPnH1Cw.tJZqy	Contador	t	2026-10-29 15:05:18	\N	2026-10-30 15:05:18	2026-10-30 15:05:18	4
\.


--
-- Data for Name: venta_lineas; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.venta_lineas (id, venta_id, producto_id, cantidad, precio_unitario, costo_unitario, subtotal, created_at, updated_at) FROM stdin;
1	1	1	4.000	530.00	195.00	2120.00	2026-10-30 12:05:18	2026-10-30 12:05:18
2	1	5	2.000	490.00	172.00	980.00	2026-10-30 12:05:18	2026-10-30 12:05:18
3	1	2	1.000	580.00	225.00	580.00	2026-10-30 12:05:18	2026-10-30 12:05:18
4	2	4	5.000	550.00	205.00	2750.00	2026-10-30 14:05:18	2026-10-30 14:05:18
5	2	2	3.750	580.00	225.00	2175.00	2026-10-30 14:05:18	2026-10-30 14:05:18
\.


--
-- Data for Name: ventas; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.ventas (id, folio, caja_turno_id, cliente_id, user_id, fecha_venta, metodo_pago, subtotal, impuestos, total, estado, created_at, updated_at) FROM stdin;
1	VTA-2026-0001	1	1	3	2026-10-30 12:05:18	Efectivo	3680.00	0.00	3680.00	Completada	2026-10-30 12:05:18	2026-10-30 12:05:18
2	VTA-2026-0002	1	2	3	2026-10-30 14:05:18	Tarjeta	4925.00	0.00	4925.00	Completada	2026-10-30 14:05:18	2026-10-30 14:05:18
\.


--
-- Name: asientos_contables_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.asientos_contables_id_seq', 6, true);


--
-- Name: bitacora_auditoria_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.bitacora_auditoria_id_seq', 3, true);


--
-- Name: cajas_turnos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.cajas_turnos_id_seq', 1, true);


--
-- Name: clientes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.clientes_id_seq', 2, true);


--
-- Name: controles_calidad_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.controles_calidad_id_seq', 1, false);


--
-- Name: cuentas_contables_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.cuentas_contables_id_seq', 12, true);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- Name: finanzas_movimientos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.finanzas_movimientos_id_seq', 3, true);


--
-- Name: folio_asientos_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.folio_asientos_seq', 179, true);


--
-- Name: folio_calidad_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.folio_calidad_seq', 34, true);


--
-- Name: folio_compras_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.folio_compras_seq', 31, true);


--
-- Name: folio_finanzas_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.folio_finanzas_seq', 40, true);


--
-- Name: folio_lotes_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.folio_lotes_seq', 117, true);


--
-- Name: folio_turnos_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.folio_turnos_seq', 5, true);


--
-- Name: folio_ventas_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.folio_ventas_seq', 22, true);


--
-- Name: insumos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.insumos_id_seq', 12, true);


--
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.jobs_id_seq', 1, false);


--
-- Name: lecturas_temperatura_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.lecturas_temperatura_id_seq', 1, false);


--
-- Name: lotes_produccion_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.lotes_produccion_id_seq', 2, true);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.migrations_id_seq', 9, true);


--
-- Name: modulos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.modulos_id_seq', 9, true);


--
-- Name: movimientos_inventario_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.movimientos_inventario_id_seq', 4, true);


--
-- Name: orden_compra_lineas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.orden_compra_lineas_id_seq', 4, true);


--
-- Name: ordenes_compra_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.ordenes_compra_id_seq', 2, true);


--
-- Name: partidas_asiento_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.partidas_asiento_id_seq', 18, true);


--
-- Name: productos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.productos_id_seq', 7, true);


--
-- Name: proveedores_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.proveedores_id_seq', 3, true);


--
-- Name: receta_insumos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.receta_insumos_id_seq', 16, true);


--
-- Name: recetas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.recetas_id_seq', 4, true);


--
-- Name: roles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.roles_id_seq', 4, true);


--
-- Name: unidades_medida_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.unidades_medida_id_seq', 3, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.users_id_seq', 4, true);


--
-- Name: venta_lineas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.venta_lineas_id_seq', 5, true);


--
-- Name: ventas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.ventas_id_seq', 2, true);


--
-- Name: asientos_contables asientos_contables_numero_asiento_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asientos_contables
    ADD CONSTRAINT asientos_contables_numero_asiento_unique UNIQUE (numero_asiento);


--
-- Name: asientos_contables asientos_contables_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asientos_contables
    ADD CONSTRAINT asientos_contables_pkey PRIMARY KEY (id);


--
-- Name: bitacora_auditoria bitacora_auditoria_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bitacora_auditoria
    ADD CONSTRAINT bitacora_auditoria_pkey PRIMARY KEY (id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: cajas_turnos cajas_turnos_codigo_turno_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cajas_turnos
    ADD CONSTRAINT cajas_turnos_codigo_turno_unique UNIQUE (codigo_turno);


--
-- Name: cajas_turnos cajas_turnos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cajas_turnos
    ADD CONSTRAINT cajas_turnos_pkey PRIMARY KEY (id);


--
-- Name: clientes clientes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.clientes
    ADD CONSTRAINT clientes_pkey PRIMARY KEY (id);


--
-- Name: controles_calidad controles_calidad_codigo_control_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.controles_calidad
    ADD CONSTRAINT controles_calidad_codigo_control_unique UNIQUE (codigo_control);


--
-- Name: controles_calidad controles_calidad_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.controles_calidad
    ADD CONSTRAINT controles_calidad_pkey PRIMARY KEY (id);


--
-- Name: cuentas_contables cuentas_contables_codigo_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cuentas_contables
    ADD CONSTRAINT cuentas_contables_codigo_unique UNIQUE (codigo);


--
-- Name: cuentas_contables cuentas_contables_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cuentas_contables
    ADD CONSTRAINT cuentas_contables_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: finanzas_movimientos finanzas_movimientos_folio_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.finanzas_movimientos
    ADD CONSTRAINT finanzas_movimientos_folio_unique UNIQUE (folio);


--
-- Name: finanzas_movimientos finanzas_movimientos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.finanzas_movimientos
    ADD CONSTRAINT finanzas_movimientos_pkey PRIMARY KEY (id);


--
-- Name: insumos insumos_codigo_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.insumos
    ADD CONSTRAINT insumos_codigo_unique UNIQUE (codigo);


--
-- Name: insumos insumos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.insumos
    ADD CONSTRAINT insumos_pkey PRIMARY KEY (id);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: lecturas_temperatura lecturas_temperatura_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.lecturas_temperatura
    ADD CONSTRAINT lecturas_temperatura_pkey PRIMARY KEY (id);


--
-- Name: lotes_produccion lotes_produccion_codigo_lote_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.lotes_produccion
    ADD CONSTRAINT lotes_produccion_codigo_lote_unique UNIQUE (codigo_lote);


--
-- Name: lotes_produccion lotes_produccion_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.lotes_produccion
    ADD CONSTRAINT lotes_produccion_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: modulos modulos_clave_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.modulos
    ADD CONSTRAINT modulos_clave_unique UNIQUE (clave);


--
-- Name: modulos modulos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.modulos
    ADD CONSTRAINT modulos_pkey PRIMARY KEY (id);


--
-- Name: movimientos_inventario movimientos_inventario_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.movimientos_inventario
    ADD CONSTRAINT movimientos_inventario_pkey PRIMARY KEY (id);


--
-- Name: orden_compra_lineas orden_compra_lineas_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.orden_compra_lineas
    ADD CONSTRAINT orden_compra_lineas_pkey PRIMARY KEY (id);


--
-- Name: ordenes_compra ordenes_compra_folio_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.ordenes_compra
    ADD CONSTRAINT ordenes_compra_folio_unique UNIQUE (folio);


--
-- Name: ordenes_compra ordenes_compra_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.ordenes_compra
    ADD CONSTRAINT ordenes_compra_pkey PRIMARY KEY (id);


--
-- Name: parametros parametros_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.parametros
    ADD CONSTRAINT parametros_pkey PRIMARY KEY (clave);


--
-- Name: partidas_asiento partidas_asiento_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.partidas_asiento
    ADD CONSTRAINT partidas_asiento_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: productos productos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.productos
    ADD CONSTRAINT productos_pkey PRIMARY KEY (id);


--
-- Name: productos productos_sku_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.productos
    ADD CONSTRAINT productos_sku_unique UNIQUE (sku);


--
-- Name: proveedores proveedores_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.proveedores
    ADD CONSTRAINT proveedores_pkey PRIMARY KEY (id);


--
-- Name: proveedores proveedores_ruc_nit_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.proveedores
    ADD CONSTRAINT proveedores_ruc_nit_unique UNIQUE (ruc_nit);


--
-- Name: receta_insumos receta_insumos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.receta_insumos
    ADD CONSTRAINT receta_insumos_pkey PRIMARY KEY (id);


--
-- Name: recetas recetas_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.recetas
    ADD CONSTRAINT recetas_pkey PRIMARY KEY (id);


--
-- Name: rol_modulo rol_modulo_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rol_modulo
    ADD CONSTRAINT rol_modulo_pkey PRIMARY KEY (rol_id, modulo_id);


--
-- Name: roles roles_nombre_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_nombre_unique UNIQUE (nombre);


--
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: unidades_medida unidades_medida_abreviatura_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.unidades_medida
    ADD CONSTRAINT unidades_medida_abreviatura_unique UNIQUE (abreviatura);


--
-- Name: unidades_medida unidades_medida_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.unidades_medida
    ADD CONSTRAINT unidades_medida_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: venta_lineas venta_lineas_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.venta_lineas
    ADD CONSTRAINT venta_lineas_pkey PRIMARY KEY (id);


--
-- Name: ventas ventas_folio_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.ventas
    ADD CONSTRAINT ventas_folio_unique UNIQUE (folio);


--
-- Name: ventas ventas_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.ventas
    ADD CONSTRAINT ventas_pkey PRIMARY KEY (id);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: failed_jobs_connection_queue_failed_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX failed_jobs_connection_queue_failed_at_index ON public.failed_jobs USING btree (connection, queue, failed_at);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: lecturas_temperatura_fecha_lectura_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX lecturas_temperatura_fecha_lectura_index ON public.lecturas_temperatura USING btree (fecha_lectura);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: asientos_contables asientos_contables_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asientos_contables
    ADD CONSTRAINT asientos_contables_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: bitacora_auditoria bitacora_auditoria_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bitacora_auditoria
    ADD CONSTRAINT bitacora_auditoria_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: cajas_turnos cajas_turnos_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cajas_turnos
    ADD CONSTRAINT cajas_turnos_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id);


--
-- Name: controles_calidad controles_calidad_lote_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.controles_calidad
    ADD CONSTRAINT controles_calidad_lote_id_foreign FOREIGN KEY (lote_id) REFERENCES public.lotes_produccion(id) ON DELETE CASCADE;


--
-- Name: controles_calidad controles_calidad_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.controles_calidad
    ADD CONSTRAINT controles_calidad_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id);


--
-- Name: finanzas_movimientos finanzas_movimientos_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.finanzas_movimientos
    ADD CONSTRAINT finanzas_movimientos_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id);


--
-- Name: insumos insumos_unidad_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.insumos
    ADD CONSTRAINT insumos_unidad_id_foreign FOREIGN KEY (unidad_id) REFERENCES public.unidades_medida(id);


--
-- Name: lotes_produccion lotes_produccion_producto_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.lotes_produccion
    ADD CONSTRAINT lotes_produccion_producto_id_foreign FOREIGN KEY (producto_id) REFERENCES public.productos(id);


--
-- Name: lotes_produccion lotes_produccion_receta_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.lotes_produccion
    ADD CONSTRAINT lotes_produccion_receta_id_foreign FOREIGN KEY (receta_id) REFERENCES public.recetas(id);


--
-- Name: lotes_produccion lotes_produccion_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.lotes_produccion
    ADD CONSTRAINT lotes_produccion_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id);


--
-- Name: movimientos_inventario movimientos_inventario_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.movimientos_inventario
    ADD CONSTRAINT movimientos_inventario_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: orden_compra_lineas orden_compra_lineas_insumo_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.orden_compra_lineas
    ADD CONSTRAINT orden_compra_lineas_insumo_id_foreign FOREIGN KEY (insumo_id) REFERENCES public.insumos(id);


--
-- Name: orden_compra_lineas orden_compra_lineas_orden_compra_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.orden_compra_lineas
    ADD CONSTRAINT orden_compra_lineas_orden_compra_id_foreign FOREIGN KEY (orden_compra_id) REFERENCES public.ordenes_compra(id) ON DELETE CASCADE;


--
-- Name: ordenes_compra ordenes_compra_proveedor_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.ordenes_compra
    ADD CONSTRAINT ordenes_compra_proveedor_id_foreign FOREIGN KEY (proveedor_id) REFERENCES public.proveedores(id);


--
-- Name: ordenes_compra ordenes_compra_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.ordenes_compra
    ADD CONSTRAINT ordenes_compra_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id);


--
-- Name: partidas_asiento partidas_asiento_asiento_contable_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.partidas_asiento
    ADD CONSTRAINT partidas_asiento_asiento_contable_id_foreign FOREIGN KEY (asiento_contable_id) REFERENCES public.asientos_contables(id) ON DELETE CASCADE;


--
-- Name: partidas_asiento partidas_asiento_cuenta_contable_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.partidas_asiento
    ADD CONSTRAINT partidas_asiento_cuenta_contable_id_foreign FOREIGN KEY (cuenta_contable_id) REFERENCES public.cuentas_contables(id) ON DELETE RESTRICT;


--
-- Name: receta_insumos receta_insumos_insumo_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.receta_insumos
    ADD CONSTRAINT receta_insumos_insumo_id_foreign FOREIGN KEY (insumo_id) REFERENCES public.insumos(id) ON DELETE RESTRICT;


--
-- Name: receta_insumos receta_insumos_receta_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.receta_insumos
    ADD CONSTRAINT receta_insumos_receta_id_foreign FOREIGN KEY (receta_id) REFERENCES public.recetas(id) ON DELETE CASCADE;


--
-- Name: recetas recetas_producto_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.recetas
    ADD CONSTRAINT recetas_producto_id_foreign FOREIGN KEY (producto_id) REFERENCES public.productos(id) ON DELETE CASCADE;


--
-- Name: rol_modulo rol_modulo_modulo_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rol_modulo
    ADD CONSTRAINT rol_modulo_modulo_id_foreign FOREIGN KEY (modulo_id) REFERENCES public.modulos(id) ON DELETE CASCADE;


--
-- Name: rol_modulo rol_modulo_rol_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rol_modulo
    ADD CONSTRAINT rol_modulo_rol_id_foreign FOREIGN KEY (rol_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: users users_rol_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_rol_id_foreign FOREIGN KEY (rol_id) REFERENCES public.roles(id) ON DELETE SET NULL;


--
-- Name: venta_lineas venta_lineas_producto_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.venta_lineas
    ADD CONSTRAINT venta_lineas_producto_id_foreign FOREIGN KEY (producto_id) REFERENCES public.productos(id);


--
-- Name: venta_lineas venta_lineas_venta_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.venta_lineas
    ADD CONSTRAINT venta_lineas_venta_id_foreign FOREIGN KEY (venta_id) REFERENCES public.ventas(id) ON DELETE CASCADE;


--
-- Name: ventas ventas_caja_turno_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.ventas
    ADD CONSTRAINT ventas_caja_turno_id_foreign FOREIGN KEY (caja_turno_id) REFERENCES public.cajas_turnos(id);


--
-- Name: ventas ventas_cliente_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.ventas
    ADD CONSTRAINT ventas_cliente_id_foreign FOREIGN KEY (cliente_id) REFERENCES public.clientes(id) ON DELETE SET NULL;


--
-- Name: ventas ventas_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.ventas
    ADD CONSTRAINT ventas_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id);


--
-- PostgreSQL database dump complete
--

\unrestrict aRICirSsDwaaa7bGrdVIuuX8vUBh5IbVipyWIhtcv5k94nSQJPMWygFhTZXWfLN

