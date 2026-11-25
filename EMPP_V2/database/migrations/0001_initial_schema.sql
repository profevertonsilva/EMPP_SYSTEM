-- EMPP v2: initial schema (specification v2.1, section 9).
--
-- Conventions
--  * Physical quantities are DOUBLE, with the unit in the column name (_um, _pa, _m_s...).
--    Penetrations reach 1e-89, far below what DECIMAL can hold.
--  * Penetration, not efficiency, is stored: efficiency near 100 % loses precision.
--  * Porosity is a fraction (0-1) everywhere.
--  * legacy_* columns link imported rows to the v1 database (bin/import-v1.php).
-- Needs MySQL 8.0.16+ or MariaDB 10.5+ (CHECK constraints, JSON, generated columns).

CREATE TABLE countries (
    id SMALLINT UNSIGNED NOT NULL PRIMARY KEY,
    iso2 CHAR(2) NOT NULL,
    iso3 CHAR(3) NULL,
    name VARCHAR(80) NOT NULL,
    UNIQUE KEY uq_countries_iso2 (iso2)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) NOT NULL,
    -- bcrypt/argon2 from password_hash(); imported v1 accounts may still hold a
    -- 40-char SHA-1, which the sign-in replaces on the first successful login
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('researcher', 'admin') NOT NULL DEFAULT 'researcher',
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    email_verified_at DATETIME NULL,
    -- one-time 6-digit code (email verification or password reset), hashed
    code_hash VARCHAR(255) NULL,
    code_purpose ENUM('verify_email', 'reset_password') NULL,
    code_expires_at DATETIME NULL,
    code_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
    name VARCHAR(150) NULL,
    institution VARCHAR(150) NULL,
    academic_level VARCHAR(100) NULL,
    purpose TEXT NULL,
    country_id SMALLINT UNSIGNED NULL,
    photo VARCHAR(100) NULL,
    legacy_login_id INT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_email (email),
    UNIQUE KEY uq_users_legacy (legacy_login_id),
    CONSTRAINT fk_users_country FOREIGN KEY (country_id) REFERENCES countries (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE membranes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    -- required when a membrane is created; NULL only on v1 imports that never had it
    thickness_um DOUBLE NULL,
    -- 'basis_weight': L derived from W / (polymer density * (1 - porosity))
    thickness_source ENUM('measured', 'basis_weight') NULL,
    basis_weight_g_m2 DOUBLE NULL,
    -- production card (optional): deposition rate = L / time, basis weight = rho * (1 - eps) * L
    deposition_time_min DOUBLE NULL,
    polymer_density_kg_m3 DOUBLE NULL,
    legacy_research_id INT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY ix_membranes_user (user_id),
    UNIQUE KEY uq_membranes_legacy (legacy_research_id),
    CONSTRAINT fk_membranes_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT ck_membranes_thickness CHECK (thickness_um IS NULL OR thickness_um > 0),
    CONSTRAINT ck_membranes_basis_weight CHECK (basis_weight_g_m2 IS NULL OR basis_weight_g_m2 > 0),
    CONSTRAINT ck_membranes_deposition CHECK (deposition_time_min IS NULL OR deposition_time_min > 0),
    CONSTRAINT ck_membranes_polymer_density CHECK (polymer_density_kg_m3 IS NULL OR polymer_density_kg_m3 > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Electrospinning parameters: optional, one row per membrane, every value nullable
CREATE TABLE spinning_params (
    membrane_id INT UNSIGNED NOT NULL PRIMARY KEY,
    voltage_kv DOUBLE NULL,
    flow_rate_ml_min DOUBLE NULL,
    distance_cm DOUBLE NULL,
    concentration_wt_pct DOUBLE NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_spinning_membrane FOREIGN KEY (membrane_id) REFERENCES membranes (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEM images: each one has its own porosity and fibre diameter
CREATE TABLE membrane_images (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    membrane_id INT UNSIGNED NOT NULL,
    file_name VARCHAR(100) NOT NULL,
    original_name VARCHAR(255) NULL,
    width_px INT UNSIGNED NULL,
    height_px INT UNSIGNED NULL,
    -- from the scale bar, which is kept in the uploaded image
    scale_um_per_px DOUBLE NULL,
    -- analysed area, without the information strip (pixels)
    crop_x INT UNSIGNED NULL,
    crop_y INT UNSIGNED NULL,
    crop_w INT UNSIGNED NULL,
    crop_h INT UNSIGNED NULL,
    porosity DOUBLE NULL,
    porosity_method ENUM('spinning_regression', 'texture_regression', 'segmentation', 'manual') NULL,
    fiber_diameter_um DOUBLE NULL,
    fiber_diameter_sd_um DOUBLE NULL,
    fiber_count INT UNSIGNED NULL,
    fiber_diameter_method ENUM('scale_bar', 'manual') NULL,
    haralick_dissimilarity DOUBLE NULL,
    haralick_correlation DOUBLE NULL,
    haralick_energy DOUBLE NULL,
    haralick_homogeneity DOUBLE NULL,
    analysis_status ENUM('pending', 'done', 'failed') NOT NULL DEFAULT 'pending',
    analysis_error VARCHAR(255) NULL,
    analyzed_at DATETIME NULL,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    legacy_research_id INT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY ix_images_membrane (membrane_id, sort_order),
    UNIQUE KEY uq_images_legacy (legacy_research_id),
    CONSTRAINT fk_images_membrane FOREIGN KEY (membrane_id) REFERENCES membranes (id) ON DELETE CASCADE,
    CONSTRAINT ck_images_porosity CHECK (porosity IS NULL OR (porosity > 0 AND porosity < 1)),
    CONSTRAINT ck_images_diameter CHECK (fiber_diameter_um IS NULL OR fiber_diameter_um > 0),
    CONSTRAINT ck_images_scale CHECK (scale_um_per_px IS NULL OR scale_um_per_px > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Individual fibre diameters measured on an image (histogram on the membrane page)
CREATE TABLE fiber_diameters (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    image_id INT UNSIGNED NOT NULL,
    diameter_um DOUBLE NOT NULL,
    KEY ix_fiber_diameters_image (image_id),
    CONSTRAINT fk_fiber_diameters_image FOREIGN KEY (image_id) REFERENCES membrane_images (id) ON DELETE CASCADE,
    CONSTRAINT ck_fiber_diameters_positive CHECK (diameter_um > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Test scenarios (seeded by 0002); editable configuration, not code
CREATE TABLE aerosol_presets (
    id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(32) NOT NULL,
    name VARCHAR(100) NOT NULL,
    standard VARCHAR(100) NOT NULL,
    aerosol VARCHAR(50) NOT NULL,
    efficiency_basis VARCHAR(100) NOT NULL,
    velocity_cm_s DOUBLE NOT NULL,
    particle_density_kg_m3 DOUBLE NOT NULL,
    cmd_um DOUBLE NOT NULL,
    gsd DOUBLE NOT NULL,
    -- counted at the MPPS (EN 1822): overall efficiency = efficiency at the MPPS
    mono TINYINT(1) NOT NULL DEFAULT 0,
    dmin_um DOUBLE NOT NULL,
    dmax_um DOUBLE NOT NULL,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    active TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY uq_aerosol_presets_code (code),
    CONSTRAINT ck_aerosol_presets_values CHECK (velocity_cm_s > 0 AND particle_density_kg_m3 > 0 AND cmd_um > 0 AND gsd > 1 AND dmin_um > 0 AND dmax_um > dmin_um)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE air_presets (
    id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(32) NOT NULL,
    name VARCHAR(100) NOT NULL,
    temperature_c DOUBLE NOT NULL,
    pressure_mmhg DOUBLE NOT NULL,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    active TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY uq_air_presets_code (code),
    CONSTRAINT ck_air_presets_values CHECK (pressure_mmhg > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A simulation run with everything needed to show it again without recomputing:
-- its full input (a snapshot: later edits to the membrane do not change it), the
-- model version and options, every result, the alerts and the grade-efficiency curve.
--   kind 'standard': the default scenario of the membrane, kept current by the
--                    application (membrane cards, Compare, correlation); one per
--                    membrane, pointed to by membranes.standard_simulation_id
--   kind 'saved':    saved by the user from the simulation screen
CREATE TABLE simulations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    membrane_id INT UNSIGNED NOT NULL,
    kind ENUM('standard', 'saved') NOT NULL,
    label VARCHAR(150) NULL,
    -- preset codes as chosen (NULL = custom values)
    aerosol_preset VARCHAR(32) NULL,
    air_preset VARCHAR(32) NULL,

    -- input
    temperature_c DOUBLE NOT NULL,
    pressure_mmhg DOUBLE NOT NULL,
    velocity_m_s DOUBLE NOT NULL,
    particle_density_kg_m3 DOUBLE NOT NULL,
    cmd_um DOUBLE NULL,
    mmd_um DOUBLE NULL,
    gsd DOUBLE NULL,
    mono TINYINT(1) NOT NULL,
    dmin_um DOUBLE NOT NULL,
    dmax_um DOUBLE NOT NULL,
    size_classes SMALLINT UNSIGNED NOT NULL,
    thickness_um DOUBLE NOT NULL,
    fiber_diameter_um DOUBLE NOT NULL,
    porosity DOUBLE NOT NULL,
    -- structure edited on the simulation screen instead of taken from the membrane
    what_if TINYINT(1) NOT NULL DEFAULT 0,
    model_version VARCHAR(10) NOT NULL,
    opt_legacy TINYINT(1) NOT NULL DEFAULT 0,
    opt_inertial TINYINT(1) NOT NULL DEFAULT 0,

    -- result
    pen DOUBLE NOT NULL,
    pen_number DOUBLE NOT NULL,
    pen_min DOUBLE NOT NULL,
    pen_03 DOUBLE NULL,
    mpps_um DOUBLE NOT NULL,
    mpps_at_edge TINYINT(1) NOT NULL,
    dp_pa DOUBLE NOT NULL,
    viscous_share DOUBLE NOT NULL,
    qf DOUBLE NOT NULL,
    qf_03 DOUBLE NULL,
    qf_mpps DOUBLE NOT NULL,
    reynolds_fiber DOUBLE NOT NULL,
    knudsen_fiber DOUBLE NOT NULL,
    kuwabara DOUBLE NOT NULL,
    k1_m2 DOUBLE NOT NULL,
    k1_davies_m2 DOUBLE NOT NULL,
    slip_factor DOUBLE NOT NULL,
    k2_m DOUBLE NOT NULL,
    air_viscosity_pa_s DOUBLE NOT NULL,
    air_density_kg_m3 DOUBLE NOT NULL,
    air_mean_free_path_m DOUBLE NOT NULL,
    d10_um DOUBLE NOT NULL,
    d50_um DOUBLE NOT NULL,
    d90_um DOUBLE NOT NULL,
    mass_outside DOUBLE NOT NULL,
    epm1 DOUBLE NULL,
    epm25 DOUBLE NULL,
    epm10 DOUBLE NULL,
    epm_group VARCHAR(60) NULL,
    -- indicative EN 1822 class (NULL below E10)
    en1822_class CHAR(3) NULL,
    check_count TINYINT UNSIGNED NOT NULL,
    -- [{level, code, text}]
    alerts JSON NOT NULL,
    -- {"d": [...], "w": [...], "wn": [...], "pen": [...]} one entry per size class
    curve JSON NOT NULL,
    -- spread across the images of the membrane: [{image_id, porosity, fiber_diameter_um, pen_min, dp_pa, en1822_class}]
    per_image JSON NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY ix_simulations_membrane (membrane_id, kind, created_at),
    CONSTRAINT fk_simulations_membrane FOREIGN KEY (membrane_id) REFERENCES membranes (id) ON DELETE CASCADE,
    CONSTRAINT ck_simulations_porosity CHECK (porosity > 0 AND porosity < 1),
    CONSTRAINT ck_simulations_pen CHECK (pen >= 0 AND pen <= 1 AND pen_min >= 0 AND pen_min <= 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- The membrane's standard simulation: one pointer, so at most one per membrane.
-- (A unique generated column on simulations is not possible: MySQL forbids
-- ON DELETE CASCADE on the base column of a stored generated column.)
ALTER TABLE membranes
    ADD COLUMN standard_simulation_id BIGINT UNSIGNED NULL AFTER polymer_density_kg_m3,
    ADD UNIQUE KEY uq_membranes_standard_simulation (standard_simulation_id),
    ADD CONSTRAINT fk_membranes_standard_simulation FOREIGN KEY (standard_simulation_id) REFERENCES simulations (id) ON DELETE SET NULL;

-- Bench measurements to compare with the prediction (Validation)
CREATE TABLE measurements (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    membrane_id INT UNSIGNED NOT NULL,
    measured_on DATE NULL,
    velocity_m_s DOUBLE NOT NULL,
    aerosol_preset VARCHAR(32) NULL,
    aerosol_description VARCHAR(150) NULL,
    dp_pa DOUBLE NULL,
    penetration DOUBLE NULL,
    -- what the measured penetration refers to
    efficiency_basis ENUM('mpps', 'mass', 'number', 'size') NULL,
    particle_size_um DOUBLE NULL,
    temperature_c DOUBLE NULL,
    pressure_mmhg DOUBLE NULL,
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY ix_measurements_membrane (membrane_id),
    CONSTRAINT fk_measurements_membrane FOREIGN KEY (membrane_id) REFERENCES membranes (id) ON DELETE CASCADE,
    CONSTRAINT ck_measurements_something CHECK (dp_pa IS NOT NULL OR penetration IS NOT NULL),
    CONSTRAINT ck_measurements_values CHECK (velocity_m_s > 0 AND (dp_pa IS NULL OR dp_pa > 0) AND (penetration IS NULL OR (penetration >= 0 AND penetration <= 1))),
    CONSTRAINT ck_measurements_size CHECK (efficiency_basis IS NULL OR efficiency_basis <> 'size' OR particle_size_um IS NOT NULL)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pressure drop measured at several velocities, to fit k1 and k2 (Validation)
CREATE TABLE dp_velocity_points (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    membrane_id INT UNSIGNED NOT NULL,
    velocity_m_s DOUBLE NOT NULL,
    dp_pa DOUBLE NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY ix_dp_points_membrane (membrane_id),
    CONSTRAINT fk_dp_points_membrane FOREIGN KEY (membrane_id) REFERENCES membranes (id) ON DELETE CASCADE,
    CONSTRAINT ck_dp_points_values CHECK (velocity_m_s > 0 AND dp_pa > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
