--
-- CREATE SCHEMA
--

CREATE SCHEMA IF NOT EXISTS adresse; 

--
-- CREATE TABLE
--

-- Commune

CREATE TABLE adresse.commune
(
    id_com serial NOT NULL,
    commune_nom text COLLATE pg_catalog."default",
    insee_code character(5) COLLATE pg_catalog."default",
    statut_com text COLLATE pg_catalog."default",
    intercom text COLLATE pg_catalog."default",
    fibre text COLLATE pg_catalog."default",
    phase_fibre character(1) COLLATE pg_catalog."default",
    actif character(3) COLLATE pg_catalog."default",
    nom_referent text COLLATE pg_catalog."default",
    tel_referent character(10) COLLATE pg_catalog."default",
    mail_referent text COLLATE pg_catalog."default",
    situation text COLLATE pg_catalog."default",
    commenter text COLLATE pg_catalog."default",
    date_delib date,
    diffusion_dgfip character(3) COLLATE pg_catalog."default",
    date_dgfip date,
    diffusion_ban character(3) COLLATE pg_catalog."default",
    date_ban date,
    geom geometry(MultiPolygon,2154),
    CONSTRAINT commune_pkey PRIMARY KEY (id_com)
)
WITH (
    OIDS = FALSE
)
TABLESPACE pg_default;

-- commune commune_deleguee

CREATE TABLE adresse.commune_deleguee
(
    id_com_del serial NOT NULL,
    commune_deleguee_nom text COLLATE pg_catalog."default",
    insee_code character(5) COLLATE pg_catalog."default",
    nom_referent_deleguee text COLLATE pg_catalog."default",
    tel_referent_deleguee character(10) COLLATE pg_catalog."default",
    mail_referent_deleguee text COLLATE pg_catalog."default",
    geom geometry(MultiPolygon,2154),
    CONSTRAINT commune_deleguee_pkey PRIMARY KEY (id_com_del)
)
WITH (
    OIDS = FALSE
)
TABLESPACE pg_default;

-- Voie

CREATE TABLE adresse.voie
(
    id_voie serial NOT NULL,
    typologie text COLLATE pg_catalog."default",
    nom text COLLATE pg_catalog."default",
    nom_complet text COLLATE pg_catalog."default",
    type_num text COLLATE pg_catalog."default",
    statut_voie_num boolean DEFAULT true,
    statut_voie boolean DEFAULT false,
    sens boolean DEFAULT false,
    achat_plaque_voie boolean DEFAULT false,
    nb_point integer,
    createur text COLLATE pg_catalog."default",
    date_creation date DEFAULT now(),
    modificateur text COLLATE pg_catalog."default",
    date_modif date,
    longueur integer,
    code_fantoir integer,
    delib boolean,
    geom geometry(LineString,2154),
    CONSTRAINT voie_pkey PRIMARY KEY (id_voie)
)
WITH (
    OIDS = FALSE
)
TABLESPACE pg_default;

-- Parcelle

CREATE TABLE adresse.parcelle
(
    fid serial NOT NULL,
    id character varying COLLATE pg_catalog."default",
    commune character varying COLLATE pg_catalog."default",
    prefixe character varying COLLATE pg_catalog."default",
    section character varying COLLATE pg_catalog."default",
    numero character varying COLLATE pg_catalog."default",
    contenance integer,
    arpente boolean,
    created date,
    updated date,
    geom geometry(MultiPolygon,2154),
    CONSTRAINT parcelle_pkey PRIMARY KEY (fid)
)
WITH (
    OIDS = FALSE
)
TABLESPACE pg_default;

-- Point adresse

CREATE TABLE adresse.point_adresse
(
    id_point serial NOT NULL,
    numero integer NOT NULL,
    suffixe text COLLATE pg_catalog."default",
    adresse_complete text COLLATE pg_catalog."default",
    code_postal character(5) COLLATE pg_catalog."default",
    type_pos text COLLATE pg_catalog."default",
    achat_plaque_numero boolean DEFAULT false,
    createur_point text COLLATE pg_catalog."default",
    date_creation_point date DEFAULT now(),
    modificateur_point text COLLATE pg_catalog."default",
    date_modif_point date,
    erreur boolean DEFAULT false,
    commentaire text COLLATE pg_catalog."default",
    geom geometry(Point,2154),
    id_voie integer,
    id_commune integer,
    id_parcelle integer,
    CONSTRAINT point_adresse_pkey PRIMARY KEY (id_point),
    CONSTRAINT point_adresse_id_commune_fkey FOREIGN KEY (id_commune)
        REFERENCES adresse.commune (id_com) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION,
    CONSTRAINT point_adresse_id_parcelle_fkey FOREIGN KEY (id_parcelle)
        REFERENCES adresse.parcelle (fid) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION,
    CONSTRAINT point_adresse_id_voie_fkey FOREIGN KEY (id_voie)
        REFERENCES adresse.voie (id_voie) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION
)
WITH (
    OIDS = FALSE
)
TABLESPACE pg_default;

-- Referencer_com

CREATE TABLE adresse.referencer_com
(
    id_com integer NOT NULL,
    id_com_deleguee integer NOT NULL,
    action text COLLATE pg_catalog."default",
    date_action date,
    CONSTRAINT referencer_com_pkey PRIMARY KEY (id_com, id_com_deleguee),
    CONSTRAINT referencer_com_id_com_deleguee_fkey FOREIGN KEY (id_com_deleguee)
        REFERENCES adresse.commune_deleguee (id_com_del) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION,
    CONSTRAINT referencer_com_id_com_fkey FOREIGN KEY (id_com)
        REFERENCES adresse.commune (id_com) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION
)
WITH (
    OIDS = FALSE
)
TABLESPACE pg_default;

-- document

CREATE TABLE adresse.document
(
    id_doc serial NOT NULL,
    nom_doc text COLLATE pg_catalog."default",
    date_doc character(5) COLLATE pg_catalog."default",
    type_document text COLLATE pg_catalog."default",
    id_commune integer,
    CONSTRAINT document_pkey PRIMARY KEY (id_doc),
    CONSTRAINT document_id_commune_fkey FOREIGN KEY (id_commune)
        REFERENCES adresse.commune (id_com) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION
)
WITH (
    OIDS = FALSE
)
TABLESPACE pg_default;

-- appartenir_com

CREATE TABLE adresse.appartenir_com
(
    id_voie integer NOT NULL,
    id_com integer NOT NULL,
    CONSTRAINT appartenir_com_pkey PRIMARY KEY (id_voie, id_com),
    CONSTRAINT appartenir_com_id_com_fkey FOREIGN KEY (id_com)
        REFERENCES adresse.commune (id_com) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION,
    CONSTRAINT appartenir_com_id_voie_fkey FOREIGN KEY (id_voie)
        REFERENCES adresse.voie (id_voie) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE NO ACTION
)
WITH (
    OIDS = FALSE
)
TABLESPACE pg_default;

--
-- CREATE Function
--

-- calcul_point_position

CREATE OR REPLACE FUNCTION adresse.calcul_point_position(
	seg geometry,
	pointc geometry)
    RETURNS boolean
    LANGUAGE 'plpgsql'
    COST 100
    VOLATILE
AS $BODY$
DECLARE
    a geometry;
    b geometry;
BEGIN
   a = ST_StartPoint(seg);
   b = ST_EndPoint(seg);
   RETURN (ST_X(b) - St_X(a))*(ST_Y(pointc) - ST_Y(a))
    - (ST_Y(b) - ST_Y(a))*(ST_X(pointc) - St_X(a))
    > 0;
END;
$BODY$;

-- calcul_segment_proche

CREATE OR REPLACE FUNCTION adresse.calcul_segment_proche(
	ligne geometry,
	pointc geometry)
    RETURNS geometry
    LANGUAGE 'plpgsql'
    COST 100
    VOLATILE
AS $BODY$
DECLARE
    lageom geometry;
BEGIN
   SELECT endgeom into lageom
   FROM ( SELECT ST_Distance(ST_MakeLine(St_PointN(ligne, n), St_PointN(ligne, n+1)), pointc) as dist,
   ST_MakeLine(St_PointN(ligne, n), St_PointN(ligne, n+1)) as endgeom
   FROM (SELECT generate_series(1, ST_NumPoints(ligne)-1) AS n) AS serie
   ORDER BY dist LIMIT 1) as finalgeom;

   RETURN lageom;
END;
$BODY$;

-- check_num_exist

CREATE OR REPLACE FUNCTION adresse.check_num_exist(
	num integer,
	suff text,
	idvoie integer)
    RETURNS boolean
    LANGUAGE 'plpgsql'
    COST 100
    VOLATILE
AS $BODY$
BEGIN
    IF (SELECT numero FROM adresse.point_adresse WHERE numero = num AND suffixe = suff AND id_voie = idvoie) IS NULL THEN
        RETURN TRUE;
    ELSE
        RETURN FALSE;
    END IF;
END;
$BODY$;

-- get_id_voie

CREATE OR REPLACE FUNCTION adresse.get_id_voie(
	pgeom geometry)
    RETURNS integer
    LANGUAGE 'plpgsql'
    COST 100
    VOLATILE
AS $BODY$
DECLARE
    idvoie integer;
BEGIN
    SELECT leid into idvoie FROM(
    SELECT v.id_voie as leid, ST_Distance(pgeom, v.geom) as dist
    FROM adresse.voie  v
    WHERE v.statut_voie_num IS FALSE ORDER BY dist LIMIT 1) AS d;

    RETURN idvoie;
END;
$BODY$;

-- calcul_num_adr

CREATE OR REPLACE FUNCTION adresse.calcul_num_adr(
	pgeom geometry)
    RETURNS TABLE(num integer, suffixe text)
    LANGUAGE 'plpgsql'
    COST 100
    VOLATILE
    ROWS 1000
AS $BODY$DECLARE
    numa integer;
    numb integer;
    numc integer;
    sens boolean;
    s text;
    rec text;
    suff text[];
    idvoie integer;
    isleft boolean;
    test boolean;
BEGIN

    SELECT adresse.calcul_point_position(adresse.calcul_segment_proche(geom, pgeom),pgeom)into isleft
    FROM( SELECT geom, id_voie, ST_Distance(pgeom, geom) as dist
    FROM adresse.voie
    WHERE statut_voie_num IS FALSE ORDER BY dist LIMIT 1) AS d;

    SELECT id_voie into idvoie
    FROM( SELECT id_voie, ST_Distance(pgeom, geom) as dist
    FROM adresse.voie
    WHERE statut_voie_num IS FALSE ORDER BY dist LIMIT 1) AS d;

    SELECT v.sens into sens
    FROM adresse.voie v WHERE v.id_voie = idvoie;

    SELECT numero into numa
    FROM(
    SELECT ST_Distance(pgeom, p1.geom) as dist, p1.numero as numero
    FROM adresse.point_adresse p1, adresse.voie v
    WHERE statut_voie_num IS FALSE AND p1.id_voie = idvoie AND
        (ST_LineLocatePoint(v.geom, ST_ClosestPoint(v.geom, pgeom)) - ST_LineLocatePoint(v.geom, ST_ClosestPoint(v.geom, p1.geom))) >0
        AND
        adresse.calcul_point_position(adresse.calcul_segment_proche(v.geom, pgeom), pgeom) =
        adresse.calcul_point_position(adresse.calcul_segment_proche(v.geom, p1.geom), p1.geom)
    ORDER BY dist LIMIT 1) AS a;

    suff = ARRAY ['bis', 'ter'];

    SELECT numero into numb
    FROM(
    SELECT ST_Distance(pgeom, p1.geom) as dist, p1.numero as numero
    FROM adresse.point_adresse p1, adresse.voie v
    WHERE statut_voie_num IS FALSE AND p1.id_voie = idvoie AND
        (ST_LineLocatePoint(v.geom, ST_ClosestPoint(v.geom, pgeom)) - ST_LineLocatePoint(v.geom, ST_ClosestPoint(v.geom, p1.geom))) <0
        AND
        adresse.calcul_point_position(adresse.calcul_segment_proche(v.geom, pgeom), pgeom) =
        adresse.calcul_point_position(adresse.calcul_segment_proche(v.geom, p1.geom), p1.geom)
    ORDER BY dist LIMIT 1) AS b;

    IF numa IS NOT NULL AND numb IS NOT NULL THEN
        test = false;
        WHILE NOT test OR numa <= numb LOOP
            FOREACH rec IN ARRAY suff LOOP
                IF (SELECT TRUE FROM adresse.point_adresse p WHERE p.id_voie = idvoie AND p.numero = numa AND p.suffixe = rec) IS NULL AND NOT test THEN
                    test = true;
                    numc = numa;
                    s = rec;
                END IF;
            END LOOP;
            numa = numa+2;
        END LOOP;
    ELSIF numa IS NOT NULL AND numb IS NULL THEN
        numc = numa+2;
    ELSIF numa IS NULL AND numb IS NOT NULL THEN
        IF numb - 2 >0 THEN
            numc =  numb - 2;
        ELSIF numb - 2 <= 0 THEN
            FOREACH rec IN ARRAY suff LOOP
                IF (SELECT TRUE FROM adresse.point_adresse p WHERE p.id_voie = idvoie AND p.numero = numb AND p.suffixe = rec) IS NULL AND NOT test THEN
                    test = true;
                    numc = numb;
                    s = rec;
                END IF;
            END LOOP;
        END IF;
    ELSIF numa IS NULL AND numb IS NULL THEN
        IF isleft AND NOT sens THEN
            numc = 1;
        ELSIF NOT isleft AND NOT sens THEN
            numc = 2;
        ELSIF isleft AND sens THEN
            numc = 2;
        ELSIF NOT isleft AND sens THEN
            numc = 1;
        END IF;
    END IF;

    return query SELECT numc, s;
END;
$BODY$;

-- calcul_num_metrique

CREATE OR REPLACE FUNCTION adresse.calcul_num_metrique(
	pgeom geometry)
    RETURNS TABLE(num integer, suffixe text)
    LANGUAGE 'plpgsql'
    COST 100
    VOLATILE
    ROWS 1000
AS $BODY$
DECLARE
    num integer;
    idvoie integer;
    numc integer;
    sens boolean;
    res text;
    rec text;
    isleft boolean;
    test boolean;
    suff text[];
BEGIN
    SELECT id_voie into idvoie FROM(
    SELECT id_voie, ST_Distance(pgeom, geom) as dist
    FROM adresse.voie
    WHERE statut_voie_num IS FALSE ORDER BY dist LIMIT 1) AS d;

    SELECT v.sens into sens FROM adresse.voie v WHERE v.id_voie = idvoie;

    SELECT adresse.calcul_point_position(adresse.calcul_segment_proche(geom, pgeom),pgeom) into isleft
    FROM( SELECT geom, id_voie, ST_Distance(pgeom, geom) as dist
    FROM adresse.voie
    WHERE statut_voie_num IS FALSE ORDER BY dist LIMIT 1) AS d;

    SELECT ST_Length(ST_MakeLine(ST_StartPoint(v.geom), ST_ClosestPoint(v.geom, pgeom)))::integer into num
    FROM adresse.voie v
    WHERE id_voie = idvoie;

    suff = ARRAY ['bis', 'ter'];

    IF isleft AND num%2 = 0 AND NOT sens THEN
        num = num +1;
    ELSIF NOT isleft AND num%2 != 0 AND NOT sens THEN
        num = num + 1;
    ELSIF isleft AND num%2 != 0 AND sens THEN
        num = num +1;
    ELSIF NOT isleft AND num%2 = 0 AND sens THEN
        num = num + 1;
    END IF;

    test = false;
    WHILE NOT test LOOP
        IF (SELECT TRUE FROM adresse.point_adresse p WHERE p.id_voie = idvoie AND numero = num) IS NULL THEN
            test = true;
            numc = num;
        ELSE
            FOREACH rec IN ARRAY suff LOOP
                IF (SELECT TRUE FROM adresse.point_adresse p WHERE p.id_voie = idvoie AND p.numero = num AND p.suffixe = rec) IS NULL AND NOT test THEN
                    test = true;
                    numc = num;
                    res = rec;
                END IF;
            END LOOP;
        END IF;
        num = num +2;
    END LOOP;

   RETURN query SELECT numc, res;
END;
$BODY$;

--
-- Trigger Function
--

-- calcul_point_voie

CREATE FUNCTION adresse.calcul_point_voie()
    RETURNS trigger
    LANGUAGE 'plpgsql'
    COST 100
    VOLATILE NOT LEAKPROOF
AS $BODY$
DECLARE
    nb integer;
BEGIN
    SELECT COUNT(id_point) into nb FROM adresse.point_adresse WHERE id_voie = NEW.id_voie;
    UPDATE adresse.voie SET nb_point = nb WHERE id_voie = NEW.id_voie;

    RETURN NEW;
END;
$BODY$;

-- trigger_point_adr

CREATE FUNCTION adresse.trigger_point_adr()
    RETURNS trigger
    LANGUAGE 'plpgsql'
    COST 100
    VOLATILE NOT LEAKPROOF
AS $BODY$DECLARE
    idvoie integer;
    adrvoie text;
BEGIN
    SELECT adresse.get_id_voie(NEW.geom) into idvoie;

    IF idvoie IS NOT NULL THEN
        IF (SELECT adresse.check_num_exist(NEW.numero, NEW.suffixe, idvoie)) THEN
            NEW.id_voie = idvoie;
            SELECT nom_complet into adrvoie FROM adresse.voie WHERE id_voie = idvoie;
            IF NEW.suffixe IS NOT NULL THEN
                NEW.adresse_complete = CONCAT(NEW.numero, ' ', NEW.suffixe, ' ', adrvoie);
            ELSE
                NEW.adresse_complete = CONCAT(NEW.numero, ' ', adrvoie);
            END IF;
            RETURN NEW;
        ELSE
            RETURN NULL;
        END IF;
    ELSE
        RETURN NULL;
    END IF;
END;
$BODY$;

-- voie_nom_complet

CREATE FUNCTION adresse.voie_nom_complet()
    RETURNS trigger
    LANGUAGE 'plpgsql'
    COST 100
    VOLATILE NOT LEAKPROOF
AS $BODY$
DECLARE

BEGIN
    NEW.nom_complet = Concat(NEW.typologie, ' ', NEW.nom);

    RETURN NEW;
END;
$BODY$;

--
-- TRIIGER
--

-- nom_complet trigger voie

CREATE TRIGGER nom_complet
    BEFORE INSERT
    ON adresse.voie
    FOR EACH ROW
    EXECUTE PROCEDURE adresse.voie_nom_complet();

-- nb_point trigger point_adresse pour voie

CREATE TRIGGER nb_point
    AFTER INSERT
    ON adresse.point_adresse
    FOR EACH ROW
    EXECUTE PROCEDURE adresse.calcul_point_voie();

-- trigger_point_adr

CREATE TRIGGER trigger_point_adr
    BEFORE INSERT
    ON adresse.point_adresse
    FOR EACH ROW
    EXECUTE PROCEDURE adresse.trigger_point_adr();
