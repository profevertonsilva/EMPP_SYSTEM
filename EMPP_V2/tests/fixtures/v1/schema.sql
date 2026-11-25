-- The v1 tables the importer reads, as they are in the live v1 database
-- (columns checked against information_schema, not the outdated Complete_DB_Script.sql).

CREATE TABLE country (
    cou_id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
    cou_iso char(2) NOT NULL,
    cou_name varchar(80) NOT NULL,
    cou_nicename varchar(80) NOT NULL,
    cou_iso3 char(3) NOT NULL
);

CREATE TABLE login (
    log_id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
    log_email varchar(100) NOT NULL,
    log_password varchar(100) NOT NULL,
    log_status enum('A','I') NOT NULL,
    log_confirmed enum('Y','N') NOT NULL,
    log_type enum('R','A') NOT NULL,
    log_token varchar(100) NULL,
    log_create timestamp NOT NULL
);

CREATE TABLE researcher (
    res_id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
    res_name varchar(100) NULL,
    res_photo varchar(100) NULL,
    res_institution varchar(100) NOT NULL,
    res_purpose text NOT NULL,
    res_academic varchar(100) NOT NULL,
    fk_country_cou_id int NULL,
    fk_login_log_id int NULL
);

CREATE TABLE research (
    ree_id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ree_name varchar(100) NOT NULL,
    ree_description text NOT NULL,
    ree_file varchar(255) NOT NULL,
    ree_flow decimal(10,2) NOT NULL,
    ree_voltage decimal(10,2) NOT NULL,
    ree_distance decimal(10,2) NOT NULL,
    ree_create timestamp NOT NULL,
    fk_login_log_id int NOT NULL
);

CREATE TABLE research_results (
    rre_id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
    rre_dissimilarity varchar(100) NOT NULL,
    rre_correlation varchar(100) NOT NULL,
    rre_energy varchar(100) NOT NULL,
    rre_homogeneity varchar(100) NOT NULL,
    rre_porosity varchar(100) NULL,
    fk_research_ree_id int NOT NULL
);

CREATE TABLE filter_simulation (
    fis_id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
    fis_material varchar(100) NOT NULL,
    fis_thickness varchar(100) NOT NULL,
    fis_diameter varchar(50) NOT NULL,
    fis_porosity varchar(50) NOT NULL,
    fis_temperature varchar(100) NOT NULL,
    fis_pressure varchar(100) NOT NULL,
    fis_velocity varchar(100) NOT NULL,
    fis_area varchar(100) NOT NULL,
    fis_density varchar(100) NOT NULL,
    fis_size_min varchar(100) NOT NULL,
    fis_size_max varchar(100) NOT NULL,
    fis_concentration varchar(100) NOT NULL,
    fis_class varchar(100) NOT NULL,
    fk_research_ree_id int NOT NULL
);
