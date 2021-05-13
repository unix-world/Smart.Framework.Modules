--
-- PostgreSQL database schema: web
-- DEPENDS ON:
-- 	* schema: smart_runtime # _sql/postgresql/init-smart-framework.sql
--

-- Started on 2021-05-12 13:17:20

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- Name: translations; Type: SCHEMA
--

CREATE SCHEMA IF NOT EXISTS web;


SET search_path = web, pg_catalog;
SET default_tablespace = '';
SET default_with_oids = false;

--
-- Name: metainfo; Type: TABLE; Schema: web
--

CREATE TABLE web.app_translations (
    lang character varying(2) NOT NULL,
    area character varying(100) NOT NULL,
    subarea character varying(100) NOT NULL,
    key character varying(100) NOT NULL,
    val character varying(1024) NOT NULL,
    is_translatable boolean DEFAULT true NOT NULL,
    counter bigint DEFAULT 0 NOT NULL,
    CONSTRAINT app_translations__chk__area CHECK ((char_length((area)::text) >= 3)),
    CONSTRAINT app_translations__chk__key CHECK ((char_length((key)::text) >= 1)),
    CONSTRAINT app_translations__chk__lang CHECK ((char_length((lang)::text) = 2)),
    CONSTRAINT app_translations__chk__subarea CHECK ((char_length((subarea)::text) >= 3))
);


ALTER TABLE ONLY web.app_translations ADD CONSTRAINT app_translations_pkey PRIMARY KEY (lang, area, subarea, key);

CREATE INDEX app_translations__idx__area ON web.app_translations USING btree (area);
CREATE INDEX app_translations__idx__is_translatable ON web.app_translations USING btree (is_translatable);
CREATE INDEX app_translations__idx__key ON web.app_translations USING btree (key);
CREATE INDEX app_translations__idx__lang ON web.app_translations USING btree (lang);
CREATE INDEX app_translations__idx__subarea ON web.app_translations USING btree (subarea);
CREATE INDEX app_translations__idx__val ON web.app_translations USING btree (val);

COMMENT ON TABLE  web.app_translations IS 'Custom Adapter Translations';
COMMENT ON COLUMN web.app_translations.lang IS 'Language ID';
COMMENT ON COLUMN web.app_translations.area IS 'Area ID';
COMMENT ON COLUMN web.app_translations.subarea IS 'SubArea ID';
COMMENT ON COLUMN web.app_translations.key IS 'Text ID';
COMMENT ON COLUMN web.app_translations.val IS 'Text Value';
COMMENT ON COLUMN web.app_translations.is_translatable IS 'Is Translatable';
COMMENT ON COLUMN web.app_translations.counter IS 'Translation Usage Counter';

--
-- # PostgreSQL
--
