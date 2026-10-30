/* Public site content managed in System settings (administrator menu).
   Shown on the public page /research. Safe to run once on an existing database. */

CREATE TABLE IF NOT EXISTS site_setting (
    set_key varchar(64) NOT NULL PRIMARY KEY,
    set_value text NULL
) DEFAULT CHARSET=utf8mb4;

/* Departments, universities and any other entity (with logo); participants point here */
CREATE TABLE IF NOT EXISTS site_institution (
    ins_id integer AUTO_INCREMENT NOT NULL PRIMARY KEY,
    ins_type enum('department','university','other') NOT NULL,
    ins_name varchar(200) NOT NULL,
    ins_acronym varchar(40) NULL,
    ins_url varchar(255) NULL,
    ins_logo varchar(100) NULL,
    ins_order integer NOT NULL DEFAULT 0
) DEFAULT CHARSET=utf8mb4;

/* Research participants: researchers and supervisors */
CREATE TABLE IF NOT EXISTS site_person (
    per_id integer AUTO_INCREMENT NOT NULL PRIMARY KEY,
    per_role enum('researcher','supervisor') NOT NULL,
    per_name varchar(150) NOT NULL,
    per_title varchar(150) NULL,
    per_email varchar(150) NULL,
    per_phone varchar(40) NULL,
    per_photo varchar(100) NULL,
    fk_site_institution_ins_id integer NULL,
    per_lattes varchar(255) NULL,
    per_orcid varchar(255) NULL,
    per_linkedin varchar(255) NULL,
    per_researchgate varchar(255) NULL,
    per_instagram varchar(255) NULL,
    per_website varchar(255) NULL,
    per_order integer NOT NULL DEFAULT 0,
    CONSTRAINT FK_site_person_institution FOREIGN KEY (fk_site_institution_ins_id)
        REFERENCES site_institution (ins_id) ON DELETE SET NULL
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS site_publication (
    pub_id integer AUTO_INCREMENT NOT NULL PRIMARY KEY,
    pub_title varchar(300) NOT NULL,
    pub_authors varchar(500) NULL,
    pub_venue varchar(200) NULL,
    pub_year varchar(4) NULL,
    pub_doi varchar(120) NULL,
    pub_url varchar(255) NULL,
    pub_order integer NOT NULL DEFAULT 0
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS site_project (
    pro_id integer AUTO_INCREMENT NOT NULL PRIMARY KEY,
    pro_title varchar(300) NOT NULL,
    pro_description text NULL,
    pro_period varchar(60) NULL,
    pro_url varchar(255) NULL,
    pro_order integer NOT NULL DEFAULT 0
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS site_funder (
    fun_id integer AUTO_INCREMENT NOT NULL PRIMARY KEY,
    fun_name varchar(200) NOT NULL,
    fun_grant varchar(120) NULL,
    fun_url varchar(255) NULL,
    fun_logo varchar(100) NULL,
    fun_order integer NOT NULL DEFAULT 0
) DEFAULT CHARSET=utf8mb4;

/* The research team named so far; complete the details in System settings */
INSERT INTO site_person (per_role, per_name, per_title, per_email, per_order)
SELECT * FROM (
    SELECT 'researcher' AS r, 'Everton Rafael da Silva' AS n, 'Doctoral researcher' AS t, 'evertonrafael@ifsp.edu.br' AS e, 1 AS o UNION ALL
    SELECT 'researcher', 'Otávio', NULL, 'otavio.costa@sou.unaerp.edu.br', 2 UNION ALL
    SELECT 'supervisor', 'Rachel', NULL, NULL, 1 UNION ALL
    SELECT 'supervisor', 'Murilo', NULL, NULL, 2
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM site_person);
