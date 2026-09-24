/* LOGICO: */

CREATE TABLE login (
    log_id integer AUTO_INCREMENT NOT NULL PRIMARY KEY,
    log_email varchar(100) NOT NULL,
    log_password varchar(100) NOT NULL,
    log_status enum('A','I') NOT NULL,
    log_confirmed enum('Y','N') NOT NULL,
    log_type enum('R','A') NOT NULL,
    log_token varchar(100) NOT NULL,
    log_create timestamp NOT NULL
);

CREATE TABLE researcher (
    res_id integer AUTO_INCREMENT NOT NULL PRIMARY KEY,
    res_name varchar(100),
    res_photo varchar(100),
    res_institution varchar(100) NOT NULL,
    res_purpose text NOT NULL,
    res_academic varchar(100) NOT NULL,
    fk_country_cou_id integer,
    fk_login_log_id integer
);

CREATE TABLE country (
    cou_id integer AUTO_INCREMENT NOT NULL PRIMARY KEY,
    cou_iso char(2) NOT NULL,
    cou_name varchar(80) NOT NULL,
    cou_nicename varchar(80) NOT NULL,
    cou_iso3 char(3) NOT NULL
);

CREATE TABLE research (
    ree_id integer AUTO_INCREMENT NOT NULL PRIMARY KEY,
    ree_name varchar(100) NOT NULL,
    ree_descrition text NOT NULL,
    ree_file varchar(100) NOT NULL,
    ree_composition varchar(50) NOT NULL,
    ree_flow decimal(10,2) NOT NULL,
    ree_voltage decimal(10,2) NOT NULL,
    ree_distance decimal(10,2) NOT NULL,
    ree_rotation decimal(10,2) NOT NULL,
    ree_translation decimal(10,2) NOT NULL,
    ree_create timestamp NOT NULL,
    fk_login_log_id integer NOT NULL
);

CREATE TABLE research_results (
    rre_id integer AUTO_INCREMENT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    rre_dissimilarity decimal(10,4) NOT NULL,
    rre_correlation decimal(10,4) NOT NULL,
    rre_energy decimal(10,4) NOT NULL,
    rre_homogeneinity decimal(10,4) NOT NULL,
    fk_research_ree_id integer NOT NULL
);

CREATE TABLE filter_distribution (
    fid_id integer AUTO_INCREMENT NOT NULL PRIMARY KEY,
    fid_dpi_min varchar(100) NOT NULL,
    fid_dpi_max varchar(100) NOT NULL,
    fld_class varchar(100) NOT NULL,
    fk_research_ree_id integer NOT NULL
);

CREATE TABLE distribution (
    dis_id integer AUTO_INCREMENT NOT NULL PRIMARY KEY,
    dis_dm varchar(100) NOT NULL,
    dis_a varchar(100) NOT NULL,
    dis_sum varchar(100) NOT NULL,
    dis_dsauter varchar(100) NOT NULL,
    dis_d50 varchar(100) NOT NULL,
    dis_d90 varchar(100) NOT NULL,
    dis_d10 varchar(100) NOT NULL,
    dis_dpi varchar(100) NOT NULL,
    fk_filter_distribution_fid_id integer NOT NULL
);

CREATE TABLE filter_simulation (
    fis_id integer AUTO_INCREMENT NOT NULL PRIMARY KEY,
    fis_material varchar(100) NOT NULL,
    fis_tickness varchar(100) NOT NULL,
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
    fk_research_ree_id integer NOT NULL
);

CREATE TABLE simulation (
    sim_id integer AUTO_INCREMENT NOT NULL PRIMARY KEY,
    sim_density varchar(100) NOT NULL,
    sim_permeability_k1 varchar(100) NOT NULL,
    sim_permeability_k2 varchar(100) NOT NULL,
    sim_gravitational varchar(100) NOT NULL,
    sim_air_density varchar(100) NOT NULL,
    sim_air_viscosity varchar(100) NOT NULL,
    sim_air_free_path varchar(100) NOT NULL,
    sim_test_pressure varchar(100) NOT NULL,
    sim_boltzmann varchar(100) NOT NULL,
    sim_kuwabara varchar(100) NOT NULL,
    sim_knudsen varchar(100) NOT NULL,
    sim_penetration varchar(100) NOT NULL,
    sim_mpps varchar(100) NOT NULL,
    sim_filter_efficiency varchar(100) NOT NULL,
    sim_flow_rate varchar(100) NOT NULL,
    sim_pressure_drop varchar(100) NOT NULL,
    sim_viscous_contribution varchar(100) NOT NULL,
    sim_inertial_contribution varchar(100) NOT NULL,
    sim_quality_factor varchar(100) NOT NULL,
    sim_concentration varchar(100) NOT NULL,
    fk_filter_simulation_fis_id integer NOT NULL
);
 
ALTER TABLE researcher ADD CONSTRAINT FK_researcher_2
    FOREIGN KEY (fk_country_cou_id)
    REFERENCES country (cou_id)
    ON DELETE CASCADE;
 
ALTER TABLE researcher ADD CONSTRAINT FK_researcher_3
    FOREIGN KEY (fk_login_log_id)
    REFERENCES login (log_id)
    ON DELETE CASCADE;
 
ALTER TABLE research ADD CONSTRAINT FK_research_2
    FOREIGN KEY (fk_login_log_id)
    REFERENCES login (log_id)
    ON DELETE CASCADE;
 
ALTER TABLE research_results ADD CONSTRAINT FK_research_results_2
    FOREIGN KEY (fk_research_ree_id)
    REFERENCES research (ree_id)
    ON DELETE CASCADE;
 
ALTER TABLE filter_distribution ADD CONSTRAINT FK_filter_distribution_2
    FOREIGN KEY (fk_research_ree_id)
    REFERENCES research (ree_id)
    ON DELETE CASCADE;
 
ALTER TABLE distribution ADD CONSTRAINT FK_distribution_2
    FOREIGN KEY (fk_filter_distribution_fid_id)
    REFERENCES filter_distribution (fid_id)
    ON DELETE CASCADE;
 
ALTER TABLE filter_simulation ADD CONSTRAINT FK_filter_simulation_2
    FOREIGN KEY (fk_research_ree_id)
    REFERENCES research (ree_id)
    ON DELETE CASCADE;
 
ALTER TABLE simulation ADD CONSTRAINT FK_simulation_2
    FOREIGN KEY (fk_filter_simulation_fis_id)
    REFERENCES filter_simulation (fis_id)
    ON DELETE CASCADE;