-- Database: csvtotable_test

-- DROP DATABASE csvtotable_test;

-- CREATE DATABASE csvtotable_test ENCODING = 'UTF8' TABLESPACE = pg_default LC_COLLATE = 'en_US.UTF-8' LC_CTYPE = 'en_US.UTF-8' CONNECTION LIMIT = -1;

-- Table: public.books

-- DROP TABLE public.books;

CREATE TABLE public.books("number" integer DEFAULT 0,book_title character varying(255),isbn character varying(255),quantity integer);
