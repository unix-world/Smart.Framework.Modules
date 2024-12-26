--
-- PostgreSQL database schema: transl_repo
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

CREATE SCHEMA transl_repo;


SET search_path = transl_repo, pg_catalog;
SET default_tablespace = '';
SET default_with_oids = false;

--
-- Name: metainfo; Type: TABLE; Schema: transl_repo
--

CREATE TABLE transl_repo.metainfo (
    id character varying(255) NOT NULL,
    val text NOT NULL,
    CONSTRAINT transl_metainfo__chk__id CHECK ((char_length((id)::text) > 0))
);

ALTER TABLE ONLY transl_repo.metainfo ADD CONSTRAINT transl_metainfo_pkey PRIMARY KEY (id);

COMMENT ON TABLE  transl_repo.metainfo IS 'Translations Repo MetaInfo';
COMMENT ON COLUMN transl_repo.metainfo.id IS 'Unique Key ID';
COMMENT ON COLUMN transl_repo.metainfo.val IS 'Data Value Text';

--
-- Name: translations; Type: TABLE; Schema: transl_repo
--

CREATE TABLE transl_repo.translations (
    id character varying(40) NOT NULL,
    txt text NOT NULL,
    transl jsonb DEFAULT '{}'::jsonb NOT NULL,
    projects jsonb DEFAULT '[]'::jsonb NOT NULL,
    created timestamp without time zone NOT NULL,
    modified timestamp without time zone NOT NULL,
    status smallint DEFAULT 0 NOT NULL,
    CONSTRAINT translations__chk__id CHECK ((char_length((id)::text) = 40)),
    CONSTRAINT translations__chk__txt CHECK ((char_length(txt) > 0))
);


ALTER TABLE ONLY transl_repo.translations ADD CONSTRAINT translations_pkey PRIMARY KEY (id);

CREATE INDEX translations__idx__created ON transl_repo.translations USING btree (created);
CREATE INDEX translations__idx__modified ON transl_repo.translations USING btree (modified);
CREATE INDEX translations__idx__projects ON transl_repo.translations USING gin (projects);
CREATE INDEX translations__idx__status ON transl_repo.translations USING btree (status);

COMMENT ON TABLE  transl_repo.metainfo IS 'Translations Repo Texts';
COMMENT ON COLUMN transl_repo.translations.id IS 'SHA1 of EN Text';
COMMENT ON COLUMN transl_repo.translations.txt IS 'The English Text';
COMMENT ON COLUMN transl_repo.translations.transl IS 'Translations Array as: { de: ''...'', ''fr'': ''...'' }';
COMMENT ON COLUMN transl_repo.translations.projects IS 'Projects Array as: [ p1, ... pz ]';
COMMENT ON COLUMN transl_repo.translations.created IS 'DateTime Created';
COMMENT ON COLUMN transl_repo.translations.modified IS 'DateTime Modified';
COMMENT ON COLUMN transl_repo.translations.status IS 'Status';

--
-- # PostgreSQL
--
