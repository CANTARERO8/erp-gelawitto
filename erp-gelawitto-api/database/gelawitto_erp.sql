--
-- PostgreSQL database dump
--

\restrict b2g1k4Ddv3i1Rj1ldx5jS8qFjgSF9asDDlutgH1xIWvjqfwHetI0kj67ehiHQUC

-- Dumped from database version 16.15 (Debian 16.15-1.pgdg13+2)
-- Dumped by pg_dump version 16.15 (Debian 16.15-1.pgdg13+2)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
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
-- Name: centros_trabajo; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.centros_trabajo (
    id bigint NOT NULL,
    codigo character varying(20) NOT NULL,
    nombre character varying(120) NOT NULL,
    estacion character varying(120),
    capacidad_unidades_hora numeric(10,2) DEFAULT '60'::numeric NOT NULL,
    activo boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: centros_trabajo_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.centros_trabajo_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: centros_trabajo_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.centros_trabajo_id_seq OWNED BY public.centros_trabajo.id;


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
-- Name: consumos_lote; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.consumos_lote (
    id bigint NOT NULL,
    lote_produccion_id bigint NOT NULL,
    lote_insumo_id bigint,
    subproducto_lote_id bigint,
    cantidad numeric(12,3) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: consumos_lote_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.consumos_lote_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: consumos_lote_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.consumos_lote_id_seq OWNED BY public.consumos_lote.id;


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
-- Name: lotes_insumo; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.lotes_insumo (
    id bigint NOT NULL,
    codigo_lote_insumo character varying(40) NOT NULL,
    insumo_id bigint NOT NULL,
    orden_compra_linea_id bigint,
    lote_proveedor character varying(50),
    cantidad_inicial numeric(12,3) NOT NULL,
    cantidad_disponible numeric(12,3) NOT NULL,
    fecha_ingreso date NOT NULL,
    fecha_vencimiento date,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: lotes_insumo_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.lotes_insumo_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: lotes_insumo_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.lotes_insumo_id_seq OWNED BY public.lotes_insumo.id;


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
    estado_calidad character varying(30) DEFAULT 'Pendiente'::character varying NOT NULL,
    cantidad_disponible numeric(12,3) DEFAULT '0'::numeric NOT NULL,
    fecha_vencimiento date,
    inicio_real timestamp(0) without time zone,
    fin_real timestamp(0) without time zone,
    tiempo_paro_min numeric(8,2) DEFAULT '0'::numeric NOT NULL
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
    updated_at timestamp(0) without time zone,
    lote_proveedor character varying(50),
    fecha_vencimiento date
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
    updated_at timestamp(0) without time zone,
    tipo character varying(25) DEFAULT 'terminado'::character varying NOT NULL
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
    insumo_id bigint,
    cantidad_requerida numeric(12,3) NOT NULL,
    merma_estimada_porcentaje numeric(5,2) DEFAULT '0'::numeric NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    subproducto_id bigint,
    nivel integer DEFAULT 1 NOT NULL
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
-- Name: rutas_operacion; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.rutas_operacion (
    id bigint NOT NULL,
    receta_id bigint NOT NULL,
    centro_trabajo_id bigint NOT NULL,
    secuencia integer DEFAULT 1 NOT NULL,
    operacion character varying(140) NOT NULL,
    tiempo_preparacion_min numeric(8,2) DEFAULT '0'::numeric NOT NULL,
    tiempo_ciclo_min_por_unidad numeric(8,3) DEFAULT '0'::numeric NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: rutas_operacion_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.rutas_operacion_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: rutas_operacion_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.rutas_operacion_id_seq OWNED BY public.rutas_operacion.id;


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
    updated_at timestamp(0) without time zone,
    lote_produccion_id bigint
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
-- Name: centros_trabajo id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.centros_trabajo ALTER COLUMN id SET DEFAULT nextval('public.centros_trabajo_id_seq'::regclass);


--
-- Name: clientes id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.clientes ALTER COLUMN id SET DEFAULT nextval('public.clientes_id_seq'::regclass);


--
-- Name: consumos_lote id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.consumos_lote ALTER COLUMN id SET DEFAULT nextval('public.consumos_lote_id_seq'::regclass);


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
-- Name: lotes_insumo id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.lotes_insumo ALTER COLUMN id SET DEFAULT nextval('public.lotes_insumo_id_seq'::regclass);


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
-- Name: rutas_operacion id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rutas_operacion ALTER COLUMN id SET DEFAULT nextval('public.rutas_operacion_id_seq'::regclass);


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
1	ASI-2026-0265	2026-11-04	Contabilidad	APE-2026	Asiento de apertura de operaciones y capital social inicial Gelawitto	70274.86	70274.86	1	2026-11-04 04:07:02	2026-11-04 04:07:02
2	ASI-2026-0266	2026-11-04	Compras	OC-2026-0001	Recepción de orden de compra OC-2026-0001 (Pagado)	2745.00	2745.00	1	2026-11-04 04:07:02	2026-11-04 04:07:02
3	ASI-2026-0267	2026-11-04	Compras	OC-2026-0002	Recepción de orden de compra OC-2026-0002 (Pagado)	9750.00	9750.00	1	2026-11-04 04:07:02	2026-11-04 04:07:02
4	ASI-2026-0268	2026-11-04	Producción	LOT-2026-001	Transformación de insumos a producto terminado (LOT-2026-001 - Base Gelificada de Arroz (Subensamble))	1950.00	1950.00	2	2026-11-04 04:07:02	2026-11-04 04:07:02
5	ASI-2026-0269	2026-11-04	Producción	LOT-2026-002	Transformación de insumos a producto terminado (LOT-2026-002 - Gelawitto Tradicional de Arroz)	623.10	623.10	2	2026-11-04 04:07:02	2026-11-04 04:07:02
6	ASI-2026-0270	2026-11-04	Producción	LOT-2026-003	Transformación de insumos a producto terminado (LOT-2026-003 - Gelawitto con Frutas Variadas)	752.50	752.50	2	2026-11-04 04:07:02	2026-11-04 04:07:02
7	ASI-2026-0271	2026-11-04	Ventas	VTA-2026-0001	Póliza de venta VTA-2026-0001 (Efectivo)	2299.70	2299.70	3	2026-11-04 04:07:02	2026-11-04 04:07:02
8	ASI-2026-0272	2026-11-04	Ventas	VTA-2026-0002	Póliza de venta VTA-2026-0002 (Tarjeta)	3053.20	3053.20	3	2026-11-04 04:07:02	2026-11-04 04:07:02
\.


--
-- Data for Name: bitacora_auditoria; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.bitacora_auditoria (id, user_id, modulo, accion, descripcion, ip_address, created_at) FROM stdin;
1	1	Seguridad	Inicialización de Sistema	Configuración de parámetros maestros, BOM de 2 niveles y centros de trabajo de Gelawitto ERP.	127.0.0.1	2026-10-30 04:07:02
2	2	Producción	Cierre de Lote	Lote LOT-2026-001 finalizado con 7.500 L de Base Gelificada de Arroz (Subensamble Nivel 1).	127.0.0.1	2026-11-01 04:07:02
3	2	Calidad	Inspección de Lote	Inspección CC-2026-006 en LOT-2026-006: 1 vaso descartado por Scrap tras alerta térmica IIoT (8.90 °C).	127.0.0.1	2026-11-04 01:07:02
4	3	Ventas y Caja	Apertura de Turno	Apertura de turno TUR-2026-001 con fondo inicial de C$ 3,500.00 y despacho FEFO por lote.	127.0.0.1	2026-11-03 22:07:02
5	1	Seguridad	Inicio de Sesión	Acceso concedido al usuario Eduardo Cantarero (Administrador).	127.0.0.1	2026-11-04 04:08:10
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
1	TUR-2026-001	3	2026-11-03 22:07:01	\N	3500.00	2995.00	2580.00	2460.00	6495.00	\N	0.00	Abierta	Turno principal punto de venta Gelawitto.	2026-11-03 22:07:01	2026-11-04 03:07:01
\.


--
-- Data for Name: centros_trabajo; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.centros_trabajo (id, codigo, nombre, estacion, capacidad_unidades_hora, activo, created_at, updated_at) FROM stdin;
1	CT-01	Estación de Cocción e Hidratación Coloidal	Área Térmica (Marmita Inoxidable)	40.00	t	2026-11-04 04:07:01	2026-11-04 04:07:01
2	CT-02	Mesa de Mezclado, Dosificación y Envasado	Área de Proceso Inocuo	120.00	t	2026-11-04 04:07:01	2026-11-04 04:07:01
3	CT-03	Cámara de Refrigeración y Gelificación Controlada (REF-01)	Cuarto Frío IIoT (2.0 a 6.0 °C)	200.00	t	2026-11-04 04:07:01	2026-11-04 04:07:01
4	CT-04	Estación de Inspección de Calidad y Etiquetado	Mesa de Liberación y Empaque Secundario	150.00	t	2026-11-04 04:07:01	2026-11-04 04:07:01
\.


--
-- Data for Name: clientes; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.clientes (id, nombre, identificacion, telefono, correo, tipo, activo, created_at, updated_at) FROM stdin;
1	Cliente de Mostrador	CF-0001	—	mostrador@gelawitto.com	Mostrador	t	2026-11-04 04:07:01	2026-11-04 04:07:01
2	Cafetería y Pastelería El Mirador	J031000077210	+505 2278 4012	compras@cafeteriaelmirador.com	Mayorista	t	2026-11-04 04:07:01	2026-11-04 04:07:01
3	Kiosco Estudiantil y Comedor Saludable UNI	J031000081905	+505 2270 1518	abastecimiento@kioscouni.edu.ni	Mayorista	t	2026-11-04 04:07:01	2026-11-04 04:07:01
\.


--
-- Data for Name: consumos_lote; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.consumos_lote (id, lote_produccion_id, lote_insumo_id, subproducto_lote_id, cantidad, created_at, updated_at) FROM stdin;
23	1	1	\N	2.000	2026-11-01 08:00:00	2026-11-01 08:00:00
24	1	3	\N	1.600	2026-11-01 08:00:00	2026-11-01 08:00:00
25	1	4	\N	20.000	2026-11-01 08:00:00	2026-11-01 08:00:00
26	1	2	\N	1.000	2026-11-01 08:00:00	2026-11-01 08:00:00
27	2	\N	1	3.000	2026-11-02 08:00:00	2026-11-02 08:00:00
28	2	6	\N	0.030	2026-11-02 08:00:00	2026-11-02 08:00:00
29	2	8	\N	30.000	2026-11-02 08:00:00	2026-11-02 08:00:00
30	2	9	\N	30.000	2026-11-02 08:00:00	2026-11-02 08:00:00
31	2	10	\N	6.000	2026-11-02 08:00:00	2026-11-02 08:00:00
32	3	\N	1	2.800	2026-11-02 13:00:00	2026-11-02 13:00:00
33	3	7	\N	0.700	2026-11-02 13:00:00	2026-11-02 13:00:00
34	3	8	\N	35.000	2026-11-02 13:00:00	2026-11-02 13:00:00
35	3	9	\N	35.000	2026-11-02 13:00:00	2026-11-02 13:00:00
36	3	10	\N	7.000	2026-11-02 13:00:00	2026-11-02 13:00:00
37	4	\N	1	2.880	2026-11-03 08:30:00	2026-11-03 08:30:00
38	4	5	\N	0.720	2026-11-03 08:30:00	2026-11-03 08:30:00
39	4	6	\N	0.036	2026-11-03 08:30:00	2026-11-03 08:30:00
40	4	8	\N	36.000	2026-11-03 08:30:00	2026-11-03 08:30:00
41	4	9	\N	36.000	2026-11-03 08:30:00	2026-11-03 08:30:00
42	4	10	\N	7.200	2026-11-03 08:30:00	2026-11-03 08:30:00
43	5	\N	1	2.500	2026-11-03 14:00:00	2026-11-03 14:00:00
44	5	6	\N	0.025	2026-11-03 14:00:00	2026-11-03 14:00:00
45	5	8	\N	25.000	2026-11-03 14:00:00	2026-11-03 14:00:00
46	5	9	\N	25.000	2026-11-03 14:00:00	2026-11-03 14:00:00
47	5	10	\N	5.000	2026-11-03 14:00:00	2026-11-03 14:00:00
48	6	\N	1	0.800	2026-11-03 23:07:01	2026-11-03 23:07:01
49	6	7	\N	0.200	2026-11-03 23:07:01	2026-11-03 23:07:01
50	6	8	\N	10.000	2026-11-03 23:07:01	2026-11-03 23:07:01
51	6	9	\N	10.000	2026-11-03 23:07:01	2026-11-03 23:07:01
52	6	10	\N	2.000	2026-11-03 23:07:01	2026-11-03 23:07:01
\.


--
-- Data for Name: controles_calidad; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.controles_calidad (id, codigo_control, lote_id, user_id, fecha_inspeccion, unidades_inspeccionadas, unidades_no_conformes, defecto, disposicion, costo_perdido, notas, created_at, updated_at) FROM stdin;
1	CC-2026-001	1	2	2026-11-01 11:00:00	20.000	0.000	\N	Liberación	0.00	Viscosidad y pH dentro de especificación.	2026-11-01 04:07:01	2026-11-01 04:07:01
2	CC-2026-002	2	2	2026-11-02 11:30:00	30.000	0.000	\N	Liberación	0.00	Gelificación firme, sellado hermético.	2026-11-02 04:07:01	2026-11-02 04:07:01
3	CC-2026-003	3	2	2026-11-02 17:00:00	35.000	0.000	\N	Liberación	0.00	Distribución uniforme de fruta en suspensión.	2026-11-02 04:07:01	2026-11-02 04:07:01
4	CC-2026-004	4	2	2026-11-03 12:20:00	36.000	0.000	\N	Liberación	0.00	Emulsión estable sin sinéresis.	2026-11-03 04:07:01	2026-11-03 04:07:01
5	CC-2026-005	5	2	2026-11-03 17:10:00	25.000	0.000	\N	Liberación	0.00	Lote conforme liberado a cámara de despacho.	2026-11-03 04:07:01	2026-11-03 04:07:01
6	CC-2026-006	6	2	2026-11-04 01:07:01	10.000	1.000	Gelificación incompleta en 1 vaso tras excursión térmica a 8.90 °C en REF-01	Scrap	21.50	9 vasos conformes liberados, 1 vaso dado de baja por Scrap.	2026-11-04 01:07:01	2026-11-04 01:07:01
\.


--
-- Data for Name: cuentas_contables; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.cuentas_contables (id, codigo, nombre, tipo, naturaleza, created_at, updated_at) FROM stdin;
1	1101	Caja y Bancos (Efectivo y Equivalentes)	Activo	Deudora	2026-11-04 04:06:59	2026-11-04 04:06:59
2	1102	Cuentas por Cobrar Clientes	Activo	Deudora	2026-11-04 04:06:59	2026-11-04 04:06:59
3	1103	IVA Acreditable (15%)	Activo	Deudora	2026-11-04 04:06:59	2026-11-04 04:06:59
4	1104	Inventario de Materia Prima e Insumos	Activo	Deudora	2026-11-04 04:06:59	2026-11-04 04:06:59
5	1105	Inventario de Producto Terminado (Helados)	Activo	Deudora	2026-11-04 04:06:59	2026-11-04 04:06:59
6	2101	Cuentas por Pagar Proveedores	Pasivo	Acreedora	2026-11-04 04:06:59	2026-11-04 04:06:59
7	2102	IVA por Pagar (15%)	Pasivo	Acreedora	2026-11-04 04:06:59	2026-11-04 04:06:59
8	3101	Capital Social y Patrimonio Inicial	Patrimonio	Acreedora	2026-11-04 04:06:59	2026-11-04 04:06:59
9	4101	Ingresos por Ventas de Helados	Ingreso	Acreedora	2026-11-04 04:06:59	2026-11-04 04:06:59
10	5101	Costo de Ventas de Producción	Costo	Deudora	2026-11-04 04:06:59	2026-11-04 04:06:59
11	5201	Gastos Operativos de Planta	Gasto	Deudora	2026-11-04 04:06:59	2026-11-04 04:06:59
12	5202	Pérdidas por Mermas y Scrap de Calidad	Gasto	Deudora	2026-11-04 04:06:59	2026-11-04 04:06:59
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
1	FIN-2026-001	2026-10-30	Egreso	Compra de Insumos	Pago factura Agroindustria Arrocera de Sébaco (OC-2026-0001)	OC-2026-0001	2745.00	Pagado	\N	1	2026-10-30 04:07:02	2026-10-30 04:07:02
2	FIN-2026-002	2026-10-31	Egreso	Compra de Insumos	Pago factura Distribuidora Alimentaria del Valle (OC-2026-0002)	OC-2026-0002	9750.00	Pagado	\N	1	2026-10-31 04:07:02	2026-10-31 04:07:02
3	FIN-2026-003	2026-11-02	Cuenta por Pagar	Proveedores	Factura a crédito 15 días Empaques Térmicos BioPack (OC-2026-0004)	OC-2026-0004	2875.00	Pendiente	2026-11-17	1	2026-11-02 04:07:02	2026-11-02 04:07:02
4	FIN-2026-004	2026-11-04	Ingreso	Ventas Mostrador y Mayorista	Recaudación acumulada turno TUR-2026-001 (VTA-0001 a VTA-0004)	TUR-2026-001	8035.00	Pagado	\N	3	2026-11-04 04:07:02	2026-11-04 04:07:02
\.


--
-- Data for Name: insumos; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.insumos (id, codigo, nombre, categoria, unidad_id, stock_actual, stock_minimo, costo_promedio, activo, created_at, updated_at) FROM stdin;
1	INS-001	Harina de arroz blanco seleccionado	Granos y Cereales	1	28.000	10.000	45.00	t	2026-11-04 04:07:01	2026-11-04 04:07:01
4	INS-004	Azúcar blanca cristalina refinada	Endulzantes	1	33.875	12.000	40.00	t	2026-11-04 04:07:01	2026-11-04 04:07:01
2	INS-002	Gelatina en polvo Great Value 40 g	Gelificantes	1	4.400	2.000	1125.00	t	2026-11-04 04:07:01	2026-11-04 04:07:01
3	INS-003	Agua purificada tratada (ósmosis inversa)	Agua	2	130.000	40.000	1.00	t	2026-11-04 04:07:01	2026-11-04 04:07:01
6	INS-006	Leche condensada azucarada pasteurizada	Lácteos y Derivados	1	9.280	10.000	135.00	t	2026-11-04 04:07:01	2026-11-04 04:07:01
7	INS-007	Extracto natural de vainilla y canela	Saborizantes	2	4.597	2.000	320.00	t	2026-11-04 04:07:01	2026-11-04 04:07:01
5	INS-005	Mix de frutas naturales en cubos (fresa, durazno y kiwi)	Frutas	1	7.100	10.000	150.00	t	2026-11-04 04:07:01	2026-11-04 04:07:01
8	INS-008	Vaso reciclable biorientado 100 mL con tapa domo	Empaques	3	114.000	60.000	8.00	t	2026-11-04 04:07:01	2026-11-04 04:07:01
9	INS-009	Etiqueta autoadhesiva impermeable Gelawitto	Empaques	3	164.000	60.000	1.50	t	2026-11-04 04:07:01	2026-11-04 04:07:01
10	INS-010	Bandeja termoformada empaque secundario x5 vasos	Empaques	3	43.633	15.000	6.00	t	2026-11-04 04:07:01	2026-11-04 04:07:01
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
1	REF-01	3.80	f	Sensor	2026-11-03 23:07:01	2026-11-03 23:07:01	2026-11-03 23:07:01
2	REF-01	4.10	f	Sensor	2026-11-04 00:07:01	2026-11-04 00:07:01	2026-11-04 00:07:01
3	REF-01	8.90	t	Sensor	2026-11-04 00:37:01	2026-11-04 00:37:01	2026-11-04 00:37:01
4	REF-01	4.20	f	Sensor	2026-11-04 02:07:01	2026-11-04 02:07:01	2026-11-04 02:07:01
\.


--
-- Data for Name: lotes_insumo; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.lotes_insumo (id, codigo_lote_insumo, insumo_id, orden_compra_linea_id, lote_proveedor, cantidad_inicial, cantidad_disponible, fecha_ingreso, fecha_vencimiento, created_at, updated_at) FROM stdin;
1	LI-2026-0001-1	1	1	LPR-ARR-2601	30.000	28.000	2026-10-30	2027-03-04	2026-10-30 04:07:01	2026-10-30 04:07:01
2	LI-2026-0001-2	4	2	LPR-AZU-2601	34.875	33.875	2026-10-30	2027-05-03	2026-10-30 04:07:01	2026-10-30 04:07:01
3	LI-2026-0002-1	2	3	LPR-GEL-2601	6.000	4.400	2026-10-31	2027-04-03	2026-10-31 04:07:01	2026-10-31 04:07:01
4	LI-2026-0002-2	3	4	LPR-AGU-2601	150.000	130.000	2026-10-31	2027-01-03	2026-10-31 04:07:01	2026-10-31 04:07:01
5	LI-2026-0002-3	6	5	LPR-LCO-2601	10.000	9.280	2026-10-31	2027-02-02	2026-10-31 04:07:01	2026-10-31 04:07:01
6	LI-2026-0002-4	7	6	LPR-VAI-2601	5.500	5.409	2026-10-31	2027-05-03	2026-10-31 04:07:01	2026-10-31 04:07:01
7	LI-2026-0003-1	5	7	LPR-FRU-2601	8.000	7.100	2026-11-01	2026-11-16	2026-11-01 04:07:01	2026-11-01 04:07:01
8	LI-2026-0004-1	8	8	LPR-VAS-2601	250.000	114.000	2026-11-02	2027-11-04	2026-11-02 04:07:01	2026-11-02 04:07:01
9	LI-2026-0004-2	9	9	LPR-ETI-2601	300.000	164.000	2026-11-02	2027-11-04	2026-11-02 04:07:01	2026-11-02 04:07:01
10	LI-2026-0004-3	10	10	LPR-BAN-2601	70.000	42.800	2026-11-02	2027-11-04	2026-11-02 04:07:01	2026-11-02 04:07:01
\.


--
-- Data for Name: lotes_produccion; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.lotes_produccion (id, codigo_lote, receta_id, producto_id, user_id, fecha_produccion, cantidad_programada, cantidad_obtenida, merma, costo_total_lote, costo_unitario_resultante, estado, notas, created_at, updated_at, estado_calidad, cantidad_disponible, fecha_vencimiento, inicio_real, fin_real, tiempo_paro_min) FROM stdin;
1	LOT-2026-001	4	4	2	2026-11-01	20.000	20.000	0.000	1950.00	97.50	Finalizado	Subensamble Nivel 1 Base Gelificada de Arroz homogénea a 85 °C.	2026-11-01 04:07:01	2026-11-01 04:07:01	Liberado	8.020	2026-11-14	2026-11-01 08:00:00	2026-11-01 14:55:00	5.00
2	LOT-2026-002	1	1	2	2026-11-02	30.000	30.000	0.000	623.10	20.77	Finalizado	Corrida estándar de Gelawitto Tradicional de Arroz.	2026-11-02 04:07:01	2026-11-02 04:07:01	Liberado	5.000	2026-11-16	2026-11-02 08:00:00	2026-11-02 11:20:00	8.00
3	LOT-2026-003	2	2	2	2026-11-02	35.000	35.000	0.000	752.50	21.50	Finalizado	Gelawitto con Frutas Variadas (20 g fruta + 80 mL base por vaso).	2026-11-02 04:07:01	2026-11-02 04:07:01	Liberado	10.000	2026-11-15	2026-11-02 13:00:00	2026-11-02 16:40:00	10.00
4	LOT-2026-004	3	3	2	2026-11-03	36.000	36.000	0.000	774.72	21.52	Finalizado	Gelawitto Tres Leches y Arroz con emulsión de leche condensada.	2026-11-03 04:07:01	2026-11-03 04:07:01	Liberado	18.000	2026-11-18	2026-11-03 08:30:00	2026-11-03 12:10:00	6.00
5	LOT-2026-005	1	1	2	2026-11-03	25.000	25.000	0.000	519.25	20.77	Finalizado	Segundo lote de Gelawitto Tradicional liberado tras inspección.	2026-11-03 04:07:01	2026-11-03 04:07:01	Liberado	20.000	2026-11-19	2026-11-03 14:00:00	2026-11-03 16:55:00	5.00
6	LOT-2026-006	2	2	2	2026-11-04	10.000	10.000	0.000	215.00	21.50	Finalizado	Inspección CC-2026-006: 1 vaso descartado por gelificación incompleta tras alerta IIoT.	2026-11-03 23:07:01	2026-11-04 01:07:01	Scrap	9.000	2026-11-18	2026-11-03 23:07:01	2026-11-04 00:52:01	5.00
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
10	2026_11_03_000100_bom_dos_niveles_rutas_oee_y_trazabilidad_fefo	1
\.


--
-- Data for Name: modulos; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.modulos (id, clave, nombre, ruta, seccion, orden, created_at, updated_at) FROM stdin;
1	dashboard	Dashboard	/dashboard	Principal	1	2026-11-04 04:06:59	2026-11-04 04:06:59
2	catalogos	Catálogos Maestros	/catalogos	Operaciones	2	2026-11-04 04:06:59	2026-11-04 04:06:59
3	compras	Compras a Proveedores	/compras	Comercial	3	2026-11-04 04:06:59	2026-11-04 04:06:59
4	inventario	Inventario y Kardex	/inventario	Operaciones	4	2026-11-04 04:06:59	2026-11-04 04:06:59
5	produccion	Recetas y Producción	/produccion	Operaciones	5	2026-11-04 04:06:59	2026-11-04 04:06:59
6	calidad	Calidad y Trazabilidad	/calidad	Operaciones	6	2026-11-04 04:06:59	2026-11-04 04:06:59
7	ventas	Ventas y Turno de Caja	/ventas	Comercial	7	2026-11-04 04:06:59	2026-11-04 04:06:59
8	finanzas	Finanzas y Cuentas	/finanzas	Finanzas	8	2026-11-04 04:06:59	2026-11-04 04:06:59
9	seguridad	Usuarios y Auditoría	/seguridad	Seguridad	9	2026-11-04 04:06:59	2026-11-04 04:06:59
\.


--
-- Data for Name: movimientos_inventario; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.movimientos_inventario (id, fecha, tipo_item, item_id, nombre_item, tipo_movimiento, referencia, cantidad, stock_resultante, costo_unitario, user_id, created_at, updated_at) FROM stdin;
20	2026-10-30 06:00:00	insumo	1	Harina de arroz blanco seleccionado	Entrada por Compra	OC-2026-0001	30.000	30.000	45.00	1	2026-10-30 06:00:00	2026-10-30 06:00:00
21	2026-10-30 06:00:00	insumo	4	Azúcar blanca cristalina refinada	Entrada por Compra	OC-2026-0001	34.875	34.875	40.00	1	2026-10-30 06:00:00	2026-10-30 06:00:00
22	2026-10-31 06:00:00	insumo	2	Gelatina en polvo Great Value 40 g	Entrada por Compra	OC-2026-0002	6.000	6.000	1125.00	1	2026-10-31 06:00:00	2026-10-31 06:00:00
23	2026-10-31 06:00:00	insumo	3	Agua purificada tratada (ósmosis inversa)	Entrada por Compra	OC-2026-0002	150.000	150.000	1.00	1	2026-10-31 06:00:00	2026-10-31 06:00:00
24	2026-10-31 06:00:00	insumo	6	Leche condensada azucarada pasteurizada	Entrada por Compra	OC-2026-0002	10.000	10.000	135.00	1	2026-10-31 06:00:00	2026-10-31 06:00:00
25	2026-10-31 06:00:00	insumo	7	Extracto natural de vainilla y canela	Entrada por Compra	OC-2026-0002	4.688	4.688	320.00	1	2026-10-31 06:00:00	2026-10-31 06:00:00
26	2026-11-01 06:00:00	insumo	5	Mix de frutas naturales en cubos (fresa, durazno y kiwi)	Entrada por Compra	OC-2026-0003	8.000	8.000	150.00	1	2026-11-01 06:00:00	2026-11-01 06:00:00
27	2026-11-01 08:00:00	insumo	1	Harina de arroz blanco seleccionado	Consumo de Producción	LOT-2026-001	-2.000	28.000	45.00	2	2026-11-01 08:00:00	2026-11-01 08:00:00
28	2026-11-01 08:00:00	insumo	2	Gelatina en polvo Great Value 40 g	Consumo de Producción	LOT-2026-001	-1.600	4.400	1125.00	2	2026-11-01 08:00:00	2026-11-01 08:00:00
29	2026-11-01 08:00:00	insumo	3	Agua purificada tratada (ósmosis inversa)	Consumo de Producción	LOT-2026-001	-20.000	130.000	1.00	2	2026-11-01 08:00:00	2026-11-01 08:00:00
30	2026-11-01 08:00:00	insumo	4	Azúcar blanca cristalina refinada	Consumo de Producción	LOT-2026-001	-1.000	33.875	40.00	2	2026-11-01 08:00:00	2026-11-01 08:00:00
31	2026-11-01 14:55:00	producto	4	Base Gelificada de Arroz (Subensamble)	Ingreso de Producción	LOT-2026-001	20.000	20.000	97.50	2	2026-11-01 14:55:00	2026-11-01 14:55:00
32	2026-11-02 06:00:00	insumo	8	Vaso reciclable biorientado 100 mL con tapa domo	Entrada por Compra	OC-2026-0004	250.000	250.000	8.00	1	2026-11-02 06:00:00	2026-11-02 06:00:00
33	2026-11-02 06:00:00	insumo	9	Etiqueta autoadhesiva impermeable Gelawitto	Entrada por Compra	OC-2026-0004	300.000	300.000	1.50	1	2026-11-02 06:00:00	2026-11-02 06:00:00
34	2026-11-02 06:00:00	insumo	10	Bandeja termoformada empaque secundario x5 vasos	Entrada por Compra	OC-2026-0004	70.833	70.833	6.00	1	2026-11-02 06:00:00	2026-11-02 06:00:00
35	2026-11-02 08:00:00	producto	4	Base Gelificada de Arroz (Subensamble)	Consumo de Producción	LOT-2026-002	-3.000	17.000	97.50	2	2026-11-02 08:00:00	2026-11-02 08:00:00
36	2026-11-02 08:00:00	insumo	7	Extracto natural de vainilla y canela	Consumo de Producción	LOT-2026-002	-0.030	4.658	320.00	2	2026-11-02 08:00:00	2026-11-02 08:00:00
37	2026-11-02 08:00:00	insumo	8	Vaso reciclable biorientado 100 mL con tapa domo	Consumo de Producción	LOT-2026-002	-30.000	220.000	8.00	2	2026-11-02 08:00:00	2026-11-02 08:00:00
38	2026-11-02 08:00:00	insumo	9	Etiqueta autoadhesiva impermeable Gelawitto	Consumo de Producción	LOT-2026-002	-30.000	270.000	1.50	2	2026-11-02 08:00:00	2026-11-02 08:00:00
39	2026-11-02 08:00:00	insumo	10	Bandeja termoformada empaque secundario x5 vasos	Consumo de Producción	LOT-2026-002	-6.000	64.833	6.00	2	2026-11-02 08:00:00	2026-11-02 08:00:00
40	2026-11-02 11:20:00	producto	1	Gelawitto Tradicional de Arroz	Ingreso de Producción	LOT-2026-002	30.000	30.000	20.77	2	2026-11-02 11:20:00	2026-11-02 11:20:00
41	2026-11-02 13:00:00	producto	4	Base Gelificada de Arroz (Subensamble)	Consumo de Producción	LOT-2026-003	-2.800	14.200	97.50	2	2026-11-02 13:00:00	2026-11-02 13:00:00
42	2026-11-02 13:00:00	insumo	5	Mix de frutas naturales en cubos (fresa, durazno y kiwi)	Consumo de Producción	LOT-2026-003	-0.700	7.300	150.00	2	2026-11-02 13:00:00	2026-11-02 13:00:00
43	2026-11-02 13:00:00	insumo	8	Vaso reciclable biorientado 100 mL con tapa domo	Consumo de Producción	LOT-2026-003	-35.000	185.000	8.00	2	2026-11-02 13:00:00	2026-11-02 13:00:00
44	2026-11-02 13:00:00	insumo	9	Etiqueta autoadhesiva impermeable Gelawitto	Consumo de Producción	LOT-2026-003	-35.000	235.000	1.50	2	2026-11-02 13:00:00	2026-11-02 13:00:00
45	2026-11-02 13:00:00	insumo	10	Bandeja termoformada empaque secundario x5 vasos	Consumo de Producción	LOT-2026-003	-7.000	57.833	6.00	2	2026-11-02 13:00:00	2026-11-02 13:00:00
46	2026-11-02 16:40:00	producto	2	Gelawitto con Frutas Variadas	Ingreso de Producción	LOT-2026-003	35.000	35.000	21.50	2	2026-11-02 16:40:00	2026-11-02 16:40:00
47	2026-11-03 08:30:00	producto	4	Base Gelificada de Arroz (Subensamble)	Consumo de Producción	LOT-2026-004	-2.880	11.320	97.50	2	2026-11-03 08:30:00	2026-11-03 08:30:00
48	2026-11-03 08:30:00	insumo	6	Leche condensada azucarada pasteurizada	Consumo de Producción	LOT-2026-004	-0.720	9.280	135.00	2	2026-11-03 08:30:00	2026-11-03 08:30:00
49	2026-11-03 08:30:00	insumo	7	Extracto natural de vainilla y canela	Consumo de Producción	LOT-2026-004	-0.036	4.622	320.00	2	2026-11-03 08:30:00	2026-11-03 08:30:00
50	2026-11-03 08:30:00	insumo	8	Vaso reciclable biorientado 100 mL con tapa domo	Consumo de Producción	LOT-2026-004	-36.000	149.000	8.00	2	2026-11-03 08:30:00	2026-11-03 08:30:00
51	2026-11-03 08:30:00	insumo	9	Etiqueta autoadhesiva impermeable Gelawitto	Consumo de Producción	LOT-2026-004	-36.000	199.000	1.50	2	2026-11-03 08:30:00	2026-11-03 08:30:00
52	2026-11-03 08:30:00	insumo	10	Bandeja termoformada empaque secundario x5 vasos	Consumo de Producción	LOT-2026-004	-7.200	50.633	6.00	2	2026-11-03 08:30:00	2026-11-03 08:30:00
53	2026-11-03 12:10:00	producto	3	Gelawitto Tres Leches y Arroz	Ingreso de Producción	LOT-2026-004	36.000	36.000	21.52	2	2026-11-03 12:10:00	2026-11-03 12:10:00
54	2026-11-03 14:00:00	producto	4	Base Gelificada de Arroz (Subensamble)	Consumo de Producción	LOT-2026-005	-2.500	8.820	97.50	2	2026-11-03 14:00:00	2026-11-03 14:00:00
55	2026-11-03 14:00:00	insumo	7	Extracto natural de vainilla y canela	Consumo de Producción	LOT-2026-005	-0.025	4.597	320.00	2	2026-11-03 14:00:00	2026-11-03 14:00:00
56	2026-11-03 14:00:00	insumo	8	Vaso reciclable biorientado 100 mL con tapa domo	Consumo de Producción	LOT-2026-005	-25.000	124.000	8.00	2	2026-11-03 14:00:00	2026-11-03 14:00:00
57	2026-11-03 14:00:00	insumo	9	Etiqueta autoadhesiva impermeable Gelawitto	Consumo de Producción	LOT-2026-005	-25.000	174.000	1.50	2	2026-11-03 14:00:00	2026-11-03 14:00:00
58	2026-11-03 14:00:00	insumo	10	Bandeja termoformada empaque secundario x5 vasos	Consumo de Producción	LOT-2026-005	-5.000	45.633	6.00	2	2026-11-03 14:00:00	2026-11-03 14:00:00
59	2026-11-03 16:55:00	producto	1	Gelawitto Tradicional de Arroz	Ingreso de Producción	LOT-2026-005	25.000	55.000	20.77	2	2026-11-03 16:55:00	2026-11-03 16:55:00
60	2026-11-03 23:07:01	producto	4	Base Gelificada de Arroz (Subensamble)	Consumo de Producción	LOT-2026-006	-0.800	8.020	97.50	2	2026-11-03 23:07:01	2026-11-03 23:07:01
61	2026-11-03 23:07:01	insumo	5	Mix de frutas naturales en cubos (fresa, durazno y kiwi)	Consumo de Producción	LOT-2026-006	-0.200	7.100	150.00	2	2026-11-03 23:07:01	2026-11-03 23:07:01
62	2026-11-03 23:07:01	insumo	8	Vaso reciclable biorientado 100 mL con tapa domo	Consumo de Producción	LOT-2026-006	-10.000	114.000	8.00	2	2026-11-03 23:07:01	2026-11-03 23:07:01
63	2026-11-03 23:07:01	insumo	9	Etiqueta autoadhesiva impermeable Gelawitto	Consumo de Producción	LOT-2026-006	-10.000	164.000	1.50	2	2026-11-03 23:07:01	2026-11-03 23:07:01
64	2026-11-03 23:07:01	insumo	10	Bandeja termoformada empaque secundario x5 vasos	Consumo de Producción	LOT-2026-006	-2.000	43.633	6.00	2	2026-11-03 23:07:01	2026-11-03 23:07:01
65	2026-11-04 00:07:01	producto	1	Gelawitto Tradicional de Arroz	Salida por Venta	VTA-2026-0001	-10.000	45.000	20.77	3	2026-11-04 00:07:01	2026-11-04 00:07:01
66	2026-11-04 00:07:01	producto	2	Gelawitto con Frutas Variadas	Salida por Venta	VTA-2026-0001	-8.000	27.000	21.50	3	2026-11-04 00:07:01	2026-11-04 00:07:01
67	2026-11-04 00:52:01	producto	2	Gelawitto con Frutas Variadas	Ingreso de Producción	LOT-2026-006	10.000	37.000	21.50	2	2026-11-04 00:52:01	2026-11-04 00:52:01
68	2026-11-04 01:07:01	producto	2	Gelawitto con Frutas Variadas	Scrap por Calidad	LOT-2026-006	-1.000	36.000	21.50	2	2026-11-04 01:07:01	2026-11-04 01:07:01
69	2026-11-04 01:07:01	producto	2	Gelawitto con Frutas Variadas	Salida por Venta	VTA-2026-0002	-12.000	24.000	21.50	3	2026-11-04 01:07:01	2026-11-04 01:07:01
70	2026-11-04 01:07:01	producto	3	Gelawitto Tres Leches y Arroz	Salida por Venta	VTA-2026-0002	-10.000	26.000	21.52	3	2026-11-04 01:07:01	2026-11-04 01:07:01
71	2026-11-04 02:07:02	producto	1	Gelawitto Tradicional de Arroz	Salida por Venta	VTA-2026-0003	-15.000	30.000	20.77	3	2026-11-04 02:07:02	2026-11-04 02:07:02
72	2026-11-04 02:07:02	producto	3	Gelawitto Tres Leches y Arroz	Salida por Venta	VTA-2026-0003	-8.000	18.000	21.52	3	2026-11-04 02:07:02	2026-11-04 02:07:02
73	2026-11-04 03:07:02	producto	1	Gelawitto Tradicional de Arroz	Salida por Venta	VTA-2026-0004	-5.000	25.000	20.77	3	2026-11-04 03:07:02	2026-11-04 03:07:02
74	2026-11-04 03:07:02	producto	2	Gelawitto con Frutas Variadas	Salida por Venta	VTA-2026-0004	-5.000	19.000	21.50	3	2026-11-04 03:07:02	2026-11-04 03:07:02
\.


--
-- Data for Name: orden_compra_lineas; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.orden_compra_lineas (id, orden_compra_id, insumo_id, cantidad, costo_unitario, subtotal, created_at, updated_at, lote_proveedor, fecha_vencimiento) FROM stdin;
1	1	1	30.000	45.00	1350.00	2026-10-30 04:07:01	2026-10-30 04:07:01	LPR-ARR-2601	2027-03-04
2	1	4	34.875	40.00	1395.00	2026-10-30 04:07:01	2026-10-30 04:07:01	LPR-AZU-2601	2027-05-03
3	2	2	6.000	1125.00	6750.00	2026-10-31 04:07:01	2026-10-31 04:07:01	LPR-GEL-2601	2027-04-03
4	2	3	150.000	1.00	150.00	2026-10-31 04:07:01	2026-10-31 04:07:01	LPR-AGU-2601	2027-01-03
5	2	6	10.000	135.00	1350.00	2026-10-31 04:07:01	2026-10-31 04:07:01	LPR-LCO-2601	2027-02-02
6	2	7	4.688	320.00	1500.00	2026-10-31 04:07:01	2026-10-31 04:07:01	LPR-VAI-2601	2027-05-03
7	3	5	8.000	150.00	1200.00	2026-11-01 04:07:01	2026-11-01 04:07:01	LPR-FRU-2601	2026-11-16
8	4	8	250.000	8.00	2000.00	2026-11-02 04:07:01	2026-11-02 04:07:01	LPR-VAS-2601	2027-11-04
9	4	9	300.000	1.50	450.00	2026-11-02 04:07:01	2026-11-02 04:07:01	LPR-ETI-2601	2027-11-04
10	4	10	70.833	6.00	425.00	2026-11-02 04:07:01	2026-11-02 04:07:01	LPR-BAN-2601	2027-11-04
\.


--
-- Data for Name: ordenes_compra; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.ordenes_compra (id, folio, proveedor_id, user_id, fecha_emision, fecha_recepcion, estado, subtotal, impuestos, total, notas, created_at, updated_at) FROM stdin;
1	OC-2026-0001	1	1	2026-10-30	2026-10-30	Recibida	2745.00	0.00	2745.00	Harina de arroz extrafina y azúcar cristalina certificadas.	2026-10-30 04:07:01	2026-10-30 04:07:01
2	OC-2026-0002	2	1	2026-10-31	2026-10-31	Recibida	9750.00	0.00	9750.00	Gelatina Great Value, agua purificada, leche condensada y vainilla.	2026-10-31 04:07:01	2026-10-31 04:07:01
3	OC-2026-0003	3	1	2026-11-01	2026-11-01	Recibida	1200.00	0.00	1200.00	Frutas tropicales frescas seleccionadas en cadena de frío.	2026-11-01 04:07:01	2026-11-01 04:07:01
4	OC-2026-0004	4	1	2026-11-02	2026-11-02	Recibida	2875.00	0.00	2875.00	Vasos 100 mL con tapa domo, etiquetas y bandejas x5.	2026-11-02 04:07:01	2026-11-02 04:07:01
\.


--
-- Data for Name: parametros; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.parametros (clave, valor, descripcion, created_at, updated_at) FROM stdin;
iva_porcentaje	15	Porcentaje de Impuesto al Valor Agregado (IVA) aplicable en compras y ventas	2026-11-04 04:06:59	2026-11-04 04:06:59
refrigerador_codigo	REF-01	Código del refrigerador industrial de maduración y enfriamiento de lotes	2026-11-04 04:06:59	2026-11-04 04:06:59
refrigerador_temp_min	2.0	Temperatura mínima aceptable en grados Celsius para el refrigerador de maduración	2026-11-04 04:06:59	2026-11-04 04:06:59
refrigerador_temp_max	6.0	Temperatura máxima aceptable en grados Celsius para el refrigerador de maduración	2026-11-04 04:06:59	2026-11-04 04:06:59
ui_tema	light	Preferencia visual de tema del sistema persistida en base de datos (light | dark)	2026-11-04 04:06:59	2026-11-04 04:06:59
ui_sidebar_colapsado	false	Estado de barra lateral de navegación persistido en base de datos (true | false)	2026-11-04 04:06:59	2026-11-04 04:06:59
\.


--
-- Data for Name: partidas_asiento; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.partidas_asiento (id, asiento_contable_id, cuenta_contable_id, descripcion, debe, haber, created_at, updated_at) FROM stdin;
1	1	1	Fondo inicial en caja y bancos	50000.00	0.00	2026-11-04 04:07:02	2026-11-04 04:07:02
2	1	4	Inventario inicial de materia prima y empaques	16570.00	0.00	2026-11-04 04:07:02	2026-11-04 04:07:02
3	1	5	Inventario inicial de producto y semielaborado	3704.86	0.00	2026-11-04 04:07:02	2026-11-04 04:07:02
4	1	8	Aportación patrimonial de socios	0.00	70274.86	2026-11-04 04:07:02	2026-11-04 04:07:02
5	2	4	Ingreso a almacén de materia prima (OC-2026-0001)	2745.00	0.00	2026-11-04 04:07:02	2026-11-04 04:07:02
6	2	1	Pago de contado OC OC-2026-0001	0.00	2745.00	2026-11-04 04:07:02	2026-11-04 04:07:02
7	3	4	Ingreso a almacén de materia prima (OC-2026-0002)	9750.00	0.00	2026-11-04 04:07:02	2026-11-04 04:07:02
8	3	1	Pago de contado OC OC-2026-0002	0.00	9750.00	2026-11-04 04:07:02	2026-11-04 04:07:02
9	4	5	Alta de producto terminado Base Gelificada de Arroz (Subensamble)	1950.00	0.00	2026-11-04 04:07:02	2026-11-04 04:07:02
10	4	4	Consumo de materia prima en lote LOT-2026-001	0.00	1950.00	2026-11-04 04:07:02	2026-11-04 04:07:02
11	5	5	Alta de producto terminado Gelawitto Tradicional de Arroz	623.10	0.00	2026-11-04 04:07:02	2026-11-04 04:07:02
12	5	4	Consumo de materia prima en lote LOT-2026-002	0.00	623.10	2026-11-04 04:07:02	2026-11-04 04:07:02
13	6	5	Alta de producto terminado Gelawitto con Frutas Variadas	752.50	0.00	2026-11-04 04:07:02	2026-11-04 04:07:02
14	6	4	Consumo de materia prima en lote LOT-2026-003	0.00	752.50	2026-11-04 04:07:02	2026-11-04 04:07:02
15	7	1	Cobro en Efectivo (VTA-2026-0001)	1920.00	0.00	2026-11-04 04:07:02	2026-11-04 04:07:02
16	7	9	Ingreso por venta VTA-2026-0001	0.00	1920.00	2026-11-04 04:07:02	2026-11-04 04:07:02
17	7	10	Reconocimiento de costo de lo vendido (VTA-2026-0001)	379.70	0.00	2026-11-04 04:07:02	2026-11-04 04:07:02
18	7	5	Descargo de inventario de helados (VTA-2026-0001)	0.00	379.70	2026-11-04 04:07:02	2026-11-04 04:07:02
19	8	1	Cobro en Tarjeta (VTA-2026-0002)	2580.00	0.00	2026-11-04 04:07:02	2026-11-04 04:07:02
20	8	9	Ingreso por venta VTA-2026-0002	0.00	2580.00	2026-11-04 04:07:02	2026-11-04 04:07:02
21	8	10	Reconocimiento de costo de lo vendido (VTA-2026-0002)	473.20	0.00	2026-11-04 04:07:02	2026-11-04 04:07:02
22	8	5	Descargo de inventario de helados (VTA-2026-0002)	0.00	473.20	2026-11-04 04:07:02	2026-11-04 04:07:02
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- Data for Name: productos; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.productos (id, sku, nombre, sabor, presentacion, precio_venta, costo_unitario, stock_actual, stock_minimo, activo, created_at, updated_at, tipo) FROM stdin;
4	SUB-GEL-BASE	Base Gelificada de Arroz (Subensamble)	Base Neutra de Arroz	Litro de base homogénea	0.00	97.50	8.020	5.000	t	2026-11-04 04:07:01	2026-11-04 04:07:01	semielaborado
1	GLW-TRA-100	Gelawitto Tradicional de Arroz	Arroz con Vainilla y Canela	Vaso 100 mL con tapa	100.00	20.77	25.000	10.000	t	2026-11-04 04:07:01	2026-11-04 04:07:01	terminado
2	GLW-FRU-100	Gelawitto con Frutas Variadas	Frutas Tropicales en Base de Arroz	Vaso 100 mL con tapa	115.00	21.50	19.000	10.000	t	2026-11-04 04:07:01	2026-11-04 04:07:01	terminado
3	GLW-LCO-100	Gelawitto Tres Leches y Arroz	Leche Condensada y Arroz	Vaso 100 mL con tapa	120.00	21.52	18.000	8.000	t	2026-11-04 04:07:01	2026-11-04 04:07:01	terminado
\.


--
-- Data for Name: proveedores; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.proveedores (id, nombre, ruc_nit, contacto, telefono, correo, direccion, activo, created_at, updated_at) FROM stdin;
1	Agroindustria Arrocera de Sébaco S.A.	J031000045129	Ing. Roberto Solís	+505 8842 1920	pedidos@arrocerasebaco.com	Km 103 Carretera Norte, Valle de Sébaco	t	2026-11-04 04:07:01	2026-11-04 04:07:01
2	Distribuidora Alimentaria del Valle S.A.	J031000062810	Lic. Patricia Morales	+505 8630 5190	ventas@alimentariadelvalle.com	Km 14 Carretera Norte, Parque Industrial	t	2026-11-04 04:07:01	2026-11-04 04:07:01
3	Frutas Selectas de la Montaña	J031000089312	María Elena Chavarría	+505 8715 4401	ventas@frutasselectas.com	Matagalpa, Bodega Central 4	t	2026-11-04 04:07:01	2026-11-04 04:07:01
4	Empaques Térmicos BioPack	J031000091204	Lic. Fernando Rivas	+505 8920 3310	comercial@biopack.com	Pista Suburbana, Módulo 12B	t	2026-11-04 04:07:01	2026-11-04 04:07:01
\.


--
-- Data for Name: receta_insumos; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.receta_insumos (id, receta_id, insumo_id, cantidad_requerida, merma_estimada_porcentaje, created_at, updated_at, subproducto_id, nivel) FROM stdin;
1	1	\N	0.500	0.00	2026-11-04 04:07:01	2026-11-04 04:07:01	4	1
2	1	7	0.005	0.00	2026-11-04 04:07:01	2026-11-04 04:07:01	\N	1
3	1	8	5.000	0.00	2026-11-04 04:07:01	2026-11-04 04:07:01	\N	1
4	1	9	5.000	0.00	2026-11-04 04:07:01	2026-11-04 04:07:01	\N	1
5	1	10	1.000	0.00	2026-11-04 04:07:01	2026-11-04 04:07:01	\N	1
6	2	\N	0.400	0.00	2026-11-04 04:07:01	2026-11-04 04:07:01	4	1
7	2	5	0.100	2.00	2026-11-04 04:07:01	2026-11-04 04:07:01	\N	1
8	2	8	5.000	0.00	2026-11-04 04:07:01	2026-11-04 04:07:01	\N	1
9	2	9	5.000	0.00	2026-11-04 04:07:01	2026-11-04 04:07:01	\N	1
10	2	10	1.000	0.00	2026-11-04 04:07:01	2026-11-04 04:07:01	\N	1
11	3	\N	0.400	0.00	2026-11-04 04:07:01	2026-11-04 04:07:01	4	1
12	3	6	0.100	0.00	2026-11-04 04:07:01	2026-11-04 04:07:01	\N	1
13	3	7	0.005	0.00	2026-11-04 04:07:01	2026-11-04 04:07:01	\N	1
14	3	8	5.000	0.00	2026-11-04 04:07:01	2026-11-04 04:07:01	\N	1
15	3	9	5.000	0.00	2026-11-04 04:07:01	2026-11-04 04:07:01	\N	1
16	3	10	1.000	0.00	2026-11-04 04:07:01	2026-11-04 04:07:01	\N	1
17	4	1	0.100	1.00	2026-11-04 04:07:01	2026-11-04 04:07:01	\N	2
18	4	2	0.080	0.00	2026-11-04 04:07:01	2026-11-04 04:07:01	\N	2
19	4	3	1.000	0.00	2026-11-04 04:07:01	2026-11-04 04:07:01	\N	2
20	4	4	0.050	0.00	2026-11-04 04:07:01	2026-11-04 04:07:01	\N	2
\.


--
-- Data for Name: recetas; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.recetas (id, producto_id, nombre, rendimiento_unidades, tiempo_preparacion_min, instrucciones, activa, created_at, updated_at) FROM stdin;
1	1	BOM 2 Niveles: Gelawitto Tradicional de Arroz (Lote 5 vasos)	5.000	43	1. Extraer 0.500 L del subensamble Base Gelificada de Arroz (SUB-GEL-BASE) e incorporar 5 mL de extracto de vainilla y canela.\n2. Dosificar exactamente 100 mL en cada uno de los 5 vasos reciclables y colocar tapa domo (CT-02).\n3. Ingresar a cámara de refrigeración REF-01 (2.0 a 6.0 °C) hasta gelificación completa (CT-03).\n4. Inspeccionar firmeza del gel, colocar etiquetas impermeables y empacar en bandeja secundaria x5 (CT-04).	t	2026-11-04 04:07:01	2026-11-04 04:07:01
2	2	BOM 2 Niveles: Gelawitto con Frutas Variadas (Lote 5 vasos)	5.000	48	1. Preparar y sanitizar 100 g de mix de frutas naturales en cubos (20 g por vaso de 100 mL).\n2. Dosificar 20 g de fruta en el fondo de cada uno de los 5 vasos reciclables (CT-02).\n3. Completar cada vaso con 80 mL de Base Gelificada de Arroz (0.400 L totales por lote de 5 vasos) y colocar tapa domo.\n4. Refrigerar en cámara fría REF-01 entre 2.0 y 6.0 °C hasta solidificación (CT-03).\n5. Inspeccionar calidad, etiquetar y disponer en bandeja termoformada x5 (CT-04).	t	2026-11-04 04:07:01	2026-11-04 04:07:01
3	3	BOM 2 Niveles: Gelawitto Tres Leches y Arroz (Lote 5 vasos)	5.000	45	1. Emulsionar 0.400 L de Base Gelificada de Arroz tibia con 100 g de leche condensada pasteurizada (20 g/vaso) y 5 mL de vainilla.\n2. Dosificar 100 mL de la emulsión homogénea en cada uno de los 5 vasos reciclables con tapa domo (CT-02).\n3. Gelificar en cámara de refrigeración REF-01 (2.0 a 6.0 °C) (CT-03).\n4. Inspeccionar ausencia de sinéresis, etiquetar y empacar en bandeja secundaria x5 (CT-04).	t	2026-11-04 04:07:01	2026-11-04 04:07:01
4	4	Subensamble Nivel 1: Base Gelificada de Arroz (Batch 1.000 L)	1.000	35	1. Pesar 100 g de harina de arroz blanco seleccionado, 80 g de gelatina en polvo y 50 g de azúcar.\n2. Calentar 500 mL de agua purificada a 85 °C en marmita de acero inoxidable (CT-01).\n3. Incorporar la harina de arroz bajo agitación continua durante 12 minutos hasta gelatinización del almidón.\n4. Disolver los 80 g de gelatina y 50 g de azúcar hasta obtener una solución coloidal sin grumos.\n5. Agregar 500 mL de agua purificada fría (choque térmico en CT-02) completando 1.000 L exacto de Base Gelificada de Arroz.	t	2026-11-04 04:07:01	2026-11-04 04:07:01
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
1	Administrador	Control total del sistema, seguridad, usuarios y auditoría	2026-11-04 04:06:59	2026-11-04 04:06:59
2	Producción	Formulación técnica, simulador MRP, lotes y control de calidad	2026-11-04 04:06:59	2026-11-04 04:06:59
3	Cajero	Apertura de turno, punto de venta y arqueo de caja	2026-11-04 04:06:59	2026-11-04 04:06:59
4	Contador	Compras, libro diario de partida doble y estados financieros	2026-11-04 04:06:59	2026-11-04 04:06:59
\.


--
-- Data for Name: rutas_operacion; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.rutas_operacion (id, receta_id, centro_trabajo_id, secuencia, operacion, tiempo_preparacion_min, tiempo_ciclo_min_por_unidad, created_at, updated_at) FROM stdin;
1	1	2	1	Aromatizado con vainilla/canela, dosificación de 100 mL por vaso y sellado	5.00	1.200	2026-11-04 04:07:01	2026-11-04 04:07:01
2	1	3	2	Gelificación controlada en cámara fría REF-01 (2.0 a 6.0 °C)	5.00	4.000	2026-11-04 04:07:01	2026-11-04 04:07:01
3	1	4	3	Inspección organoléptica, etiquetado impermeable y empaque en bandeja x5	3.00	0.800	2026-11-04 04:07:01	2026-11-04 04:07:01
4	2	2	1	Dosificación de 20 g de fruta en cubos + 80 mL de base gelificada de arroz por vaso	8.00	1.600	2026-11-04 04:07:01	2026-11-04 04:07:01
5	2	3	2	Gelificación controlada en cámara fría REF-01 (2.0 a 6.0 °C)	5.00	4.000	2026-11-04 04:07:01	2026-11-04 04:07:01
6	2	4	3	Inspección de suspensión de fruta, etiquetado y empaque en bandeja x5	3.00	0.800	2026-11-04 04:07:01	2026-11-04 04:07:01
7	3	2	1	Incorporación de 20 g de leche condensada + 80 mL de base de arroz por vaso	6.00	1.400	2026-11-04 04:07:01	2026-11-04 04:07:01
8	3	3	2	Gelificación controlada en cámara fría REF-01 (2.0 a 6.0 °C)	5.00	4.000	2026-11-04 04:07:01	2026-11-04 04:07:01
9	3	4	3	Inspección de fases, etiquetado impermeable y empaque en bandeja x5	3.00	0.800	2026-11-04 04:07:01	2026-11-04 04:07:01
10	4	1	1	Cocción coloidal de harina de arroz e hidratación de gelatina en 500 mL de agua a 85 °C	10.00	15.000	2026-11-04 04:07:01	2026-11-04 04:07:01
11	4	2	2	Choque térmico con 500 mL de agua fría, endulzado y homogeneizado de 1.000 L de base	5.00	5.000	2026-11-04 04:07:01	2026-11-04 04:07:01
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
bbzXr6dZPkATxiI9ia7chPt5awhYuDUNrntr3sPa	1	127.0.0.1	Python-urllib/3.14	eyJfdG9rZW4iOiJHejlDZFAxMEpFc2pEU3hzTDhjVE96OVhONFQzRUxBUkVNbVpkOGsxIiwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjEsInBhc3N3b3JkX2hhc2hfd2ViIjoiNDc0NTlkZjNjY2JiMzA1ODUzMDYxZDAyNjgzMzJkN2UwZWQ3ZDMzYTdiYmUyZTU4ZDllYWZiMWI2OTc1MDRjYyIsIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwXC9hcGlcL2Rhc2hib2FyZCIsInJvdXRlIjoiYXBpLmRhc2hib2FyZCJ9fQ==	1793765296
\.


--
-- Data for Name: unidades_medida; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.unidades_medida (id, nombre, abreviatura, created_at, updated_at) FROM stdin;
1	Kilogramo	kg	2026-11-04 04:07:01	2026-11-04 04:07:01
2	Litro	L	2026-11-04 04:07:01	2026-11-04 04:07:01
3	Unidad	ud	2026-11-04 04:07:01	2026-11-04 04:07:01
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.users (id, name, email, email_verified_at, password, rol, activo, ultimo_acceso, remember_token, created_at, updated_at, rol_id) FROM stdin;
2	Moises Zelaya	produccion@gelawitto.com	\N	$2y$12$DytKOdMtA.8IbAuUD1hE.eC87ETFdA/K/OnMLfQSz/PId7Y6xcHYG	Producción	t	2026-11-04 02:07:00	\N	2026-11-04 04:07:00	2026-11-04 04:07:00	2
3	Valeria Mendoza	caja@gelawitto.com	\N	$2y$12$iit5zyBekcalxnPkZl5Bne1L.Misj/EwIdcC0xdwEkvAJ0jiXNMcy	Cajero	t	2026-11-04 03:32:00	\N	2026-11-04 04:07:00	2026-11-04 04:07:00	3
4	Carlos Navarrete	finanzas@gelawitto.com	\N	$2y$12$mi8y.Rh7k2H1U8dF3mZ/3eVOHYFq7epwBfIOVGScDG72D5kJjZj3O	Contador	t	2026-11-03 04:07:01	\N	2026-11-04 04:07:01	2026-11-04 04:07:01	4
1	Eduardo Cantarero	admin@gelawitto.com	\N	$2y$12$wQrkiwbJG/w1ARgof70NRu6L0UtZe1KnbtskOj6wTxHRhCQh3UNfq	Administrador	t	2026-11-04 04:08:10	\N	2026-11-04 04:07:00	2026-11-04 04:08:10	1
\.


--
-- Data for Name: venta_lineas; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.venta_lineas (id, venta_id, producto_id, cantidad, precio_unitario, costo_unitario, subtotal, created_at, updated_at, lote_produccion_id) FROM stdin;
1	1	1	10.000	100.00	20.77	1000.00	2026-11-04 00:07:01	2026-11-04 00:07:01	2
2	1	2	8.000	115.00	21.50	920.00	2026-11-04 00:07:01	2026-11-04 00:07:01	3
3	2	2	12.000	115.00	21.50	1380.00	2026-11-04 01:07:02	2026-11-04 01:07:02	3
4	2	3	10.000	120.00	21.52	1200.00	2026-11-04 01:07:02	2026-11-04 01:07:02	4
5	3	1	15.000	100.00	20.77	1500.00	2026-11-04 02:07:02	2026-11-04 02:07:02	2
6	3	3	8.000	120.00	21.52	960.00	2026-11-04 02:07:02	2026-11-04 02:07:02	4
7	4	1	5.000	100.00	20.77	500.00	2026-11-04 03:07:02	2026-11-04 03:07:02	5
8	4	2	5.000	115.00	21.50	575.00	2026-11-04 03:07:02	2026-11-04 03:07:02	3
\.


--
-- Data for Name: ventas; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.ventas (id, folio, caja_turno_id, cliente_id, user_id, fecha_venta, metodo_pago, subtotal, impuestos, total, estado, created_at, updated_at) FROM stdin;
1	VTA-2026-0001	1	1	3	2026-11-04 00:07:01	Efectivo	1920.00	0.00	1920.00	Completada	2026-11-04 00:07:01	2026-11-04 00:07:01
2	VTA-2026-0002	1	2	3	2026-11-04 01:07:01	Tarjeta	2580.00	0.00	2580.00	Completada	2026-11-04 01:07:01	2026-11-04 01:07:01
3	VTA-2026-0003	1	3	3	2026-11-04 02:07:02	Transferencia	2460.00	0.00	2460.00	Completada	2026-11-04 02:07:02	2026-11-04 02:07:02
4	VTA-2026-0004	1	2	3	2026-11-04 03:07:02	Efectivo	1075.00	0.00	1075.00	Completada	2026-11-04 03:07:02	2026-11-04 03:07:02
\.


--
-- Name: asientos_contables_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.asientos_contables_id_seq', 8, true);


--
-- Name: bitacora_auditoria_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.bitacora_auditoria_id_seq', 5, true);


--
-- Name: cajas_turnos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.cajas_turnos_id_seq', 1, true);


--
-- Name: centros_trabajo_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.centros_trabajo_id_seq', 4, true);


--
-- Name: clientes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.clientes_id_seq', 3, true);


--
-- Name: consumos_lote_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.consumos_lote_id_seq', 52, true);


--
-- Name: controles_calidad_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.controles_calidad_id_seq', 6, true);


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

SELECT pg_catalog.setval('public.finanzas_movimientos_id_seq', 4, true);


--
-- Name: folio_asientos_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.folio_asientos_seq', 272, true);


--
-- Name: folio_calidad_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.folio_calidad_seq', 62, true);


--
-- Name: folio_compras_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.folio_compras_seq', 50, true);


--
-- Name: folio_finanzas_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.folio_finanzas_seq', 66, true);


--
-- Name: folio_lotes_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.folio_lotes_seq', 165, true);


--
-- Name: folio_turnos_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.folio_turnos_seq', 7, true);


--
-- Name: folio_ventas_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.folio_ventas_seq', 35, true);


--
-- Name: insumos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.insumos_id_seq', 10, true);


--
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.jobs_id_seq', 1, false);


--
-- Name: lecturas_temperatura_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.lecturas_temperatura_id_seq', 4, true);


--
-- Name: lotes_insumo_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.lotes_insumo_id_seq', 10, true);


--
-- Name: lotes_produccion_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.lotes_produccion_id_seq', 6, true);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.migrations_id_seq', 10, true);


--
-- Name: modulos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.modulos_id_seq', 9, true);


--
-- Name: movimientos_inventario_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.movimientos_inventario_id_seq', 74, true);


--
-- Name: orden_compra_lineas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.orden_compra_lineas_id_seq', 10, true);


--
-- Name: ordenes_compra_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.ordenes_compra_id_seq', 4, true);


--
-- Name: partidas_asiento_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.partidas_asiento_id_seq', 22, true);


--
-- Name: productos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.productos_id_seq', 4, true);


--
-- Name: proveedores_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.proveedores_id_seq', 4, true);


--
-- Name: receta_insumos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.receta_insumos_id_seq', 20, true);


--
-- Name: recetas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.recetas_id_seq', 4, true);


--
-- Name: roles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.roles_id_seq', 4, true);


--
-- Name: rutas_operacion_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.rutas_operacion_id_seq', 11, true);


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

SELECT pg_catalog.setval('public.venta_lineas_id_seq', 8, true);


--
-- Name: ventas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.ventas_id_seq', 4, true);


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
-- Name: centros_trabajo centros_trabajo_codigo_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.centros_trabajo
    ADD CONSTRAINT centros_trabajo_codigo_unique UNIQUE (codigo);


--
-- Name: centros_trabajo centros_trabajo_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.centros_trabajo
    ADD CONSTRAINT centros_trabajo_pkey PRIMARY KEY (id);


--
-- Name: clientes clientes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.clientes
    ADD CONSTRAINT clientes_pkey PRIMARY KEY (id);


--
-- Name: consumos_lote consumos_lote_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.consumos_lote
    ADD CONSTRAINT consumos_lote_pkey PRIMARY KEY (id);


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
-- Name: lotes_insumo lotes_insumo_codigo_lote_insumo_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.lotes_insumo
    ADD CONSTRAINT lotes_insumo_codigo_lote_insumo_unique UNIQUE (codigo_lote_insumo);


--
-- Name: lotes_insumo lotes_insumo_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.lotes_insumo
    ADD CONSTRAINT lotes_insumo_pkey PRIMARY KEY (id);


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
-- Name: rutas_operacion rutas_operacion_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rutas_operacion
    ADD CONSTRAINT rutas_operacion_pkey PRIMARY KEY (id);


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
-- Name: consumos_lote consumos_lote_lote_insumo_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.consumos_lote
    ADD CONSTRAINT consumos_lote_lote_insumo_id_foreign FOREIGN KEY (lote_insumo_id) REFERENCES public.lotes_insumo(id) ON DELETE SET NULL;


--
-- Name: consumos_lote consumos_lote_lote_produccion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.consumos_lote
    ADD CONSTRAINT consumos_lote_lote_produccion_id_foreign FOREIGN KEY (lote_produccion_id) REFERENCES public.lotes_produccion(id) ON DELETE CASCADE;


--
-- Name: consumos_lote consumos_lote_subproducto_lote_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.consumos_lote
    ADD CONSTRAINT consumos_lote_subproducto_lote_id_foreign FOREIGN KEY (subproducto_lote_id) REFERENCES public.lotes_produccion(id) ON DELETE SET NULL;


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
-- Name: lotes_insumo lotes_insumo_insumo_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.lotes_insumo
    ADD CONSTRAINT lotes_insumo_insumo_id_foreign FOREIGN KEY (insumo_id) REFERENCES public.insumos(id) ON DELETE CASCADE;


--
-- Name: lotes_insumo lotes_insumo_orden_compra_linea_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.lotes_insumo
    ADD CONSTRAINT lotes_insumo_orden_compra_linea_id_foreign FOREIGN KEY (orden_compra_linea_id) REFERENCES public.orden_compra_lineas(id) ON DELETE SET NULL;


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
-- Name: receta_insumos receta_insumos_subproducto_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.receta_insumos
    ADD CONSTRAINT receta_insumos_subproducto_id_foreign FOREIGN KEY (subproducto_id) REFERENCES public.productos(id) ON DELETE SET NULL;


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
-- Name: rutas_operacion rutas_operacion_centro_trabajo_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rutas_operacion
    ADD CONSTRAINT rutas_operacion_centro_trabajo_id_foreign FOREIGN KEY (centro_trabajo_id) REFERENCES public.centros_trabajo(id) ON DELETE RESTRICT;


--
-- Name: rutas_operacion rutas_operacion_receta_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rutas_operacion
    ADD CONSTRAINT rutas_operacion_receta_id_foreign FOREIGN KEY (receta_id) REFERENCES public.recetas(id) ON DELETE CASCADE;


--
-- Name: users users_rol_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_rol_id_foreign FOREIGN KEY (rol_id) REFERENCES public.roles(id) ON DELETE SET NULL;


--
-- Name: venta_lineas venta_lineas_lote_produccion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.venta_lineas
    ADD CONSTRAINT venta_lineas_lote_produccion_id_foreign FOREIGN KEY (lote_produccion_id) REFERENCES public.lotes_produccion(id) ON DELETE SET NULL;


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

\unrestrict b2g1k4Ddv3i1Rj1ldx5jS8qFjgSF9asDDlutgH1xIWvjqfwHetI0kj67ehiHQUC

