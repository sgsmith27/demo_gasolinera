--
-- PostgreSQL database dump
--

\restrict fVF71HftvZslhlcYO821RmCh5D4aP0piUKmTbLRrmacxXxoNuOTGuPvrMjBvT3o

-- Dumped from database version 18.3
-- Dumped by pg_dump version 18.3

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
-- Name: cache; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache OWNER TO postgres;

--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO postgres;

--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.failed_jobs OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.failed_jobs_id_seq OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: fuel_deliveries; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.fuel_deliveries (
    id bigint NOT NULL,
    delivered_at timestamp(0) without time zone NOT NULL,
    tank_id bigint NOT NULL,
    fuel_id bigint NOT NULL,
    liters numeric(14,3) NOT NULL,
    total_cost_q numeric(14,2),
    created_by bigint,
    notes character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.fuel_deliveries OWNER TO postgres;

--
-- Name: fuel_deliveries_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.fuel_deliveries_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.fuel_deliveries_id_seq OWNER TO postgres;

--
-- Name: fuel_deliveries_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.fuel_deliveries_id_seq OWNED BY public.fuel_deliveries.id;


--
-- Name: fuel_prices; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.fuel_prices (
    id bigint NOT NULL,
    fuel_id bigint NOT NULL,
    price_per_liter numeric(10,4) NOT NULL,
    valid_from timestamp(0) without time zone NOT NULL,
    created_by bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.fuel_prices OWNER TO postgres;

--
-- Name: fuel_prices_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.fuel_prices_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.fuel_prices_id_seq OWNER TO postgres;

--
-- Name: fuel_prices_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.fuel_prices_id_seq OWNED BY public.fuel_prices.id;


--
-- Name: fuels; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.fuels (
    id bigint NOT NULL,
    name character varying(50) NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.fuels OWNER TO postgres;

--
-- Name: fuels_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.fuels_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.fuels_id_seq OWNER TO postgres;

--
-- Name: fuels_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.fuels_id_seq OWNED BY public.fuels.id;


--
-- Name: inventory_movements; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.inventory_movements (
    id bigint NOT NULL,
    moved_at timestamp(0) without time zone NOT NULL,
    tank_id bigint NOT NULL,
    fuel_id bigint NOT NULL,
    movement_type character varying(10) NOT NULL,
    liters_delta numeric(14,3) NOT NULL,
    reference_type character varying(10) DEFAULT 'NONE'::character varying NOT NULL,
    reference_id bigint,
    created_by bigint,
    notes character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.inventory_movements OWNER TO postgres;

--
-- Name: inventory_movements_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.inventory_movements_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.inventory_movements_id_seq OWNER TO postgres;

--
-- Name: inventory_movements_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.inventory_movements_id_seq OWNED BY public.inventory_movements.id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: postgres
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


ALTER TABLE public.job_batches OWNER TO postgres;

--
-- Name: jobs; Type: TABLE; Schema: public; Owner: postgres
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


ALTER TABLE public.jobs OWNER TO postgres;

--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.jobs_id_seq OWNER TO postgres;

--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: nozzles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.nozzles (
    id bigint NOT NULL,
    pump_id bigint NOT NULL,
    fuel_id bigint NOT NULL,
    code character varying(30) NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.nozzles OWNER TO postgres;

--
-- Name: nozzles_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.nozzles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.nozzles_id_seq OWNER TO postgres;

--
-- Name: nozzles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.nozzles_id_seq OWNED BY public.nozzles.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO postgres;

--
-- Name: pumps; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.pumps (
    id bigint NOT NULL,
    code character varying(20) NOT NULL,
    name character varying(100),
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.pumps OWNER TO postgres;

--
-- Name: pumps_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.pumps_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.pumps_id_seq OWNER TO postgres;

--
-- Name: pumps_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.pumps_id_seq OWNED BY public.pumps.id;


--
-- Name: sales; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sales (
    id bigint NOT NULL,
    sold_at timestamp(0) without time zone NOT NULL,
    user_id bigint NOT NULL,
    nozzle_id bigint NOT NULL,
    fuel_id bigint NOT NULL,
    price_per_liter numeric(10,4) NOT NULL,
    liters numeric(14,3) NOT NULL,
    total_amount_q numeric(14,2) NOT NULL,
    sale_mode character varying(10) NOT NULL,
    payment_method character varying(20) DEFAULT 'cash'::character varying NOT NULL,
    notes character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.sales OWNER TO postgres;

--
-- Name: sales_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.sales_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.sales_id_seq OWNER TO postgres;

--
-- Name: sales_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.sales_id_seq OWNED BY public.sales.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO postgres;

--
-- Name: tanks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tanks (
    id bigint NOT NULL,
    fuel_id bigint NOT NULL,
    name character varying(100),
    capacity_liters numeric(14,3),
    current_liters numeric(14,3) DEFAULT '0'::numeric NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.tanks OWNER TO postgres;

--
-- Name: tanks_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tanks_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tanks_id_seq OWNER TO postgres;

--
-- Name: tanks_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tanks_id_seq OWNED BY public.tanks.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.users OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: fuel_deliveries id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_deliveries ALTER COLUMN id SET DEFAULT nextval('public.fuel_deliveries_id_seq'::regclass);


--
-- Name: fuel_prices id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_prices ALTER COLUMN id SET DEFAULT nextval('public.fuel_prices_id_seq'::regclass);


--
-- Name: fuels id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuels ALTER COLUMN id SET DEFAULT nextval('public.fuels_id_seq'::regclass);


--
-- Name: inventory_movements id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.inventory_movements ALTER COLUMN id SET DEFAULT nextval('public.inventory_movements_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: nozzles id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.nozzles ALTER COLUMN id SET DEFAULT nextval('public.nozzles_id_seq'::regclass);


--
-- Name: pumps id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pumps ALTER COLUMN id SET DEFAULT nextval('public.pumps_id_seq'::regclass);


--
-- Name: sales id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sales ALTER COLUMN id SET DEFAULT nextval('public.sales_id_seq'::regclass);


--
-- Name: tanks id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tanks ALTER COLUMN id SET DEFAULT nextval('public.tanks_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache (key, value, expiration) FROM stdin;
\.


--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- Data for Name: fuel_deliveries; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.fuel_deliveries (id, delivered_at, tank_id, fuel_id, liters, total_cost_q, created_by, notes, created_at, updated_at) FROM stdin;
1	2026-03-05 09:00:04	1	1	200.000	6500.00	1	Pipa prueba	2026-03-05 09:00:04	2026-03-05 09:00:04
2	2026-03-05 09:43:26	2	2	200.000	7200.00	1	Carga inicial Super	2026-03-05 09:43:26	2026-03-05 09:43:26
3	2026-03-05 09:43:26	3	3	200.000	6500.00	1	Carga inicial Diesel	2026-03-05 09:43:26	2026-03-05 09:43:26
\.


--
-- Data for Name: fuel_prices; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.fuel_prices (id, fuel_id, price_per_liter, valid_from, created_by, created_at, updated_at) FROM stdin;
1	1	33.5000	2026-03-05 08:42:45	1	2026-03-05 08:43:46	2026-03-05 08:43:46
2	2	35.9000	2026-03-05 08:42:45	1	2026-03-05 08:43:46	2026-03-05 08:43:46
3	3	31.2000	2026-03-05 08:42:45	1	2026-03-05 08:43:46	2026-03-05 08:43:46
4	1	33.5499	2026-03-05 10:06:58	1	2026-03-05 10:06:58	2026-03-05 10:06:58
\.


--
-- Data for Name: fuels; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.fuels (id, name, is_active, created_at, updated_at) FROM stdin;
1	Gasolina Regular	t	2026-03-05 07:57:29	2026-03-05 07:57:29
2	Gasolina Super	t	2026-03-05 07:57:29	2026-03-05 07:57:29
3	Diesel	t	2026-03-05 07:57:29	2026-03-05 07:57:29
\.


--
-- Data for Name: inventory_movements; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.inventory_movements (id, moved_at, tank_id, fuel_id, movement_type, liters_delta, reference_type, reference_id, created_by, notes, created_at, updated_at) FROM stdin;
1	2026-03-05 08:49:18	1	1	OUT	-2.985	SALE	1	1	\N	2026-03-05 08:49:18	2026-03-05 08:49:18
2	2026-03-05 09:00:04	1	1	IN	200.000	PIPA	1	1	Pipa prueba	2026-03-05 09:00:04	2026-03-05 09:00:04
3	2026-03-05 09:35:19	1	1	OUT	-7.463	SALE	2	1	\N	2026-03-05 09:35:19	2026-03-05 09:35:19
4	2026-03-05 09:36:15	1	1	OUT	-8.955	SALE	3	1	\N	2026-03-05 09:36:15	2026-03-05 09:36:15
5	2026-03-05 09:36:42	1	1	OUT	-1.493	SALE	4	1	\N	2026-03-05 09:36:42	2026-03-05 09:36:42
6	2026-03-05 09:36:52	1	1	OUT	-1.761	SALE	5	1	\N	2026-03-05 09:36:52	2026-03-05 09:36:52
7	2026-03-05 09:43:26	2	2	IN	200.000	PIPA	2	1	Carga inicial Super	2026-03-05 09:43:26	2026-03-05 09:43:26
8	2026-03-05 09:43:26	3	3	IN	200.000	PIPA	3	1	Carga inicial Diesel	2026-03-05 09:43:26	2026-03-05 09:43:26
9	2026-03-05 09:44:19	3	3	OUT	-8.013	SALE	6	1	\N	2026-03-05 09:44:19	2026-03-05 09:44:19
10	2026-03-05 09:44:28	2	2	OUT	-13.928	SALE	7	1	\N	2026-03-05 09:44:28	2026-03-05 09:44:28
11	2026-03-05 09:59:09	1	1	OUT	-7.571	SALE	8	1	\N	2026-03-05 09:59:09	2026-03-05 09:59:09
12	2026-03-05 10:31:40	3	3	OUT	-7.571	SALE	9	1	\N	2026-03-05 10:31:40	2026-03-05 10:31:40
13	2026-03-05 10:43:19	3	3	OUT	-18.927	SALE	10	1	\N	2026-03-05 10:43:19	2026-03-05 10:43:19
\.


--
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) FROM stdin;
\.


--
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.jobs (id, queue, payload, attempts, reserved_at, available_at, created_at) FROM stdin;
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	0001_01_01_000000_create_users_table	1
2	0001_01_01_000001_create_cache_table	1
3	0001_01_01_000002_create_jobs_table	1
4	2026_03_05_074816_create_fuels_table	2
5	2026_03_05_074828_create_pumps_table	2
6	2026_03_05_074829_create_nozzles_table	2
7	2026_03_05_074829_create_tanks_table	2
8	2026_03_05_074830_create_fuel_prices_table	2
9	2026_03_05_080001_create_sales_table	3
10	2026_03_05_080002_create_inventory_movements_table	3
11	2026_03_05_085336_create_fuel_deliveries_table	4
\.


--
-- Data for Name: nozzles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.nozzles (id, pump_id, fuel_id, code, is_active, created_at, updated_at) FROM stdin;
1	1	1	B1-REG	t	2026-03-05 08:42:15	2026-03-05 08:42:15
2	1	2	B1-SUP	t	2026-03-05 08:42:15	2026-03-05 08:42:15
3	1	3	B1-DIE	t	2026-03-05 08:42:15	2026-03-05 08:42:15
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- Data for Name: pumps; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.pumps (id, code, name, is_active, created_at, updated_at) FROM stdin;
1	B1	Bomba 1	t	2026-03-05 08:27:16	2026-03-05 08:27:16
\.


--
-- Data for Name: sales; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sales (id, sold_at, user_id, nozzle_id, fuel_id, price_per_liter, liters, total_amount_q, sale_mode, payment_method, notes, created_at, updated_at) FROM stdin;
1	2026-03-05 08:49:18	1	1	1	33.5000	2.985	100.00	amount	cash	\N	2026-03-05 08:49:18	2026-03-05 08:49:18
2	2026-03-05 09:35:19	1	1	1	33.5000	7.463	250.00	amount	cash	\N	2026-03-05 09:35:19	2026-03-05 09:35:19
3	2026-03-05 09:36:15	1	1	1	33.5000	8.955	300.00	amount	cash	\N	2026-03-05 09:36:15	2026-03-05 09:36:15
4	2026-03-05 09:36:42	1	1	1	33.5000	1.493	50.00	amount	card	\N	2026-03-05 09:36:42	2026-03-05 09:36:42
5	2026-03-05 09:36:52	1	1	1	33.5000	1.761	59.00	amount	transfer	\N	2026-03-05 09:36:52	2026-03-05 09:36:52
6	2026-03-05 09:44:19	1	3	3	31.2000	8.013	250.00	amount	cash	\N	2026-03-05 09:44:19	2026-03-05 09:44:19
7	2026-03-05 09:44:28	1	2	2	35.9000	13.928	500.00	amount	cash	\N	2026-03-05 09:44:28	2026-03-05 09:44:28
8	2026-03-05 09:59:09	1	1	1	33.5000	7.571	253.62	volume	cash	\N	2026-03-05 09:59:09	2026-03-05 09:59:09
9	2026-03-05 10:31:40	1	3	3	31.2000	7.571	236.21	volume	cash	\N	2026-03-05 10:31:40	2026-03-05 10:31:40
10	2026-03-05 10:43:19	1	3	3	31.2000	18.927	590.52	volume	cash	\N	2026-03-05 10:43:19	2026-03-05 10:43:19
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
dlkeTEhqhKOBzKnhHPyqzd9DmcP45cxS7buuioXc	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoieFZnSE5QUDFmbzdwVEtEUHpJdFo1UXA2bk50UmI1WmpuZG5LdVVVaCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9zYWxlcy9uZXciO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1772707375
lqxIAdp6X2LVSTHcTVst5ntZf9zFszmGknvvNDM4	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiVDlHdEUzckdYWHJGMVZ5OWduZEpNdDFwcmlwZExYMHJMb3F2aTFMSCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9zYWxlcy9uZXciO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1772754147
VQP65yeHoXcT4KaBldw06OmD4FyMyzEhZZso8zpE	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiY1dPQ3pGYlFDaWhpN0VIZk5PMHE0QzRkaXFQVldSUUd2VzZFazlQMSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9zYWxlcy9uZXciO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1772766603
\.


--
-- Data for Name: tanks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tanks (id, fuel_id, name, capacity_liters, current_liters, is_active, created_at, updated_at) FROM stdin;
2	2	Tanque - Gasolina Super	\N	186.072	t	2026-03-05 07:57:29	2026-03-05 09:44:28
1	1	Tanque - Gasolina Regular	\N	669.772	t	2026-03-05 07:57:29	2026-03-05 09:59:09
3	3	Tanque - Diesel	\N	165.489	t	2026-03-05 07:57:29	2026-03-05 10:43:19
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at) FROM stdin;
1	Admin	admin@gasolinera.local	\N	$2y$12$.Iar8z6SJCsylgvbrgRb1uOAj37wNAurYWbY/YOrgGrR3OZllYrzK	\N	2026-03-05 08:15:43	2026-03-05 08:15:43
\.


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- Name: fuel_deliveries_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.fuel_deliveries_id_seq', 3, true);


--
-- Name: fuel_prices_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.fuel_prices_id_seq', 4, true);


--
-- Name: fuels_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.fuels_id_seq', 3, true);


--
-- Name: inventory_movements_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.inventory_movements_id_seq', 13, true);


--
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.jobs_id_seq', 1, false);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.migrations_id_seq', 11, true);


--
-- Name: nozzles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.nozzles_id_seq', 3, true);


--
-- Name: pumps_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.pumps_id_seq', 1, true);


--
-- Name: sales_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.sales_id_seq', 10, true);


--
-- Name: tanks_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tanks_id_seq', 3, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_seq', 1, true);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: fuel_deliveries fuel_deliveries_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_deliveries
    ADD CONSTRAINT fuel_deliveries_pkey PRIMARY KEY (id);


--
-- Name: fuel_prices fuel_prices_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_prices
    ADD CONSTRAINT fuel_prices_pkey PRIMARY KEY (id);


--
-- Name: fuels fuels_name_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuels
    ADD CONSTRAINT fuels_name_unique UNIQUE (name);


--
-- Name: fuels fuels_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuels
    ADD CONSTRAINT fuels_pkey PRIMARY KEY (id);


--
-- Name: inventory_movements inventory_movements_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.inventory_movements
    ADD CONSTRAINT inventory_movements_pkey PRIMARY KEY (id);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: nozzles nozzles_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.nozzles
    ADD CONSTRAINT nozzles_code_unique UNIQUE (code);


--
-- Name: nozzles nozzles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.nozzles
    ADD CONSTRAINT nozzles_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: pumps pumps_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pumps
    ADD CONSTRAINT pumps_code_unique UNIQUE (code);


--
-- Name: pumps pumps_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pumps
    ADD CONSTRAINT pumps_pkey PRIMARY KEY (id);


--
-- Name: sales sales_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sales
    ADD CONSTRAINT sales_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: tanks tanks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tanks
    ADD CONSTRAINT tanks_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: fuel_deliveries_delivered_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX fuel_deliveries_delivered_at_index ON public.fuel_deliveries USING btree (delivered_at);


--
-- Name: fuel_deliveries_tank_id_delivered_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX fuel_deliveries_tank_id_delivered_at_index ON public.fuel_deliveries USING btree (tank_id, delivered_at);


--
-- Name: fuel_prices_fuel_id_valid_from_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX fuel_prices_fuel_id_valid_from_index ON public.fuel_prices USING btree (fuel_id, valid_from);


--
-- Name: inventory_movements_fuel_id_moved_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX inventory_movements_fuel_id_moved_at_index ON public.inventory_movements USING btree (fuel_id, moved_at);


--
-- Name: inventory_movements_reference_type_reference_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX inventory_movements_reference_type_reference_id_index ON public.inventory_movements USING btree (reference_type, reference_id);


--
-- Name: inventory_movements_tank_id_moved_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX inventory_movements_tank_id_moved_at_index ON public.inventory_movements USING btree (tank_id, moved_at);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: nozzles_pump_id_fuel_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX nozzles_pump_id_fuel_id_index ON public.nozzles USING btree (pump_id, fuel_id);


--
-- Name: sales_fuel_id_sold_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sales_fuel_id_sold_at_index ON public.sales USING btree (fuel_id, sold_at);


--
-- Name: sales_nozzle_id_sold_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sales_nozzle_id_sold_at_index ON public.sales USING btree (nozzle_id, sold_at);


--
-- Name: sales_sold_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sales_sold_at_index ON public.sales USING btree (sold_at);


--
-- Name: sales_user_id_sold_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sales_user_id_sold_at_index ON public.sales USING btree (user_id, sold_at);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: tanks_fuel_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX tanks_fuel_id_index ON public.tanks USING btree (fuel_id);


--
-- Name: fuel_deliveries fuel_deliveries_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_deliveries
    ADD CONSTRAINT fuel_deliveries_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: fuel_deliveries fuel_deliveries_fuel_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_deliveries
    ADD CONSTRAINT fuel_deliveries_fuel_id_foreign FOREIGN KEY (fuel_id) REFERENCES public.fuels(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: fuel_deliveries fuel_deliveries_tank_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_deliveries
    ADD CONSTRAINT fuel_deliveries_tank_id_foreign FOREIGN KEY (tank_id) REFERENCES public.tanks(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: fuel_prices fuel_prices_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_prices
    ADD CONSTRAINT fuel_prices_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: fuel_prices fuel_prices_fuel_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fuel_prices
    ADD CONSTRAINT fuel_prices_fuel_id_foreign FOREIGN KEY (fuel_id) REFERENCES public.fuels(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: inventory_movements inventory_movements_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.inventory_movements
    ADD CONSTRAINT inventory_movements_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: inventory_movements inventory_movements_fuel_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.inventory_movements
    ADD CONSTRAINT inventory_movements_fuel_id_foreign FOREIGN KEY (fuel_id) REFERENCES public.fuels(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: inventory_movements inventory_movements_tank_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.inventory_movements
    ADD CONSTRAINT inventory_movements_tank_id_foreign FOREIGN KEY (tank_id) REFERENCES public.tanks(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: nozzles nozzles_fuel_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.nozzles
    ADD CONSTRAINT nozzles_fuel_id_foreign FOREIGN KEY (fuel_id) REFERENCES public.fuels(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: nozzles nozzles_pump_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.nozzles
    ADD CONSTRAINT nozzles_pump_id_foreign FOREIGN KEY (pump_id) REFERENCES public.pumps(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: sales sales_fuel_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sales
    ADD CONSTRAINT sales_fuel_id_foreign FOREIGN KEY (fuel_id) REFERENCES public.fuels(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: sales sales_nozzle_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sales
    ADD CONSTRAINT sales_nozzle_id_foreign FOREIGN KEY (nozzle_id) REFERENCES public.nozzles(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: sales sales_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sales
    ADD CONSTRAINT sales_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: tanks tanks_fuel_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tanks
    ADD CONSTRAINT tanks_fuel_id_foreign FOREIGN KEY (fuel_id) REFERENCES public.fuels(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- PostgreSQL database dump complete
--

\unrestrict fVF71HftvZslhlcYO821RmCh5D4aP0piUKmTbLRrmacxXxoNuOTGuPvrMjBvT3o

