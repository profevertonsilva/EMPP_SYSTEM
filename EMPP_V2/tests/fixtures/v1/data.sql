-- Small v1 dataset covering the cases the importer must handle

INSERT INTO country (cou_id, cou_iso, cou_name, cou_nicename, cou_iso3) VALUES
    (30, 'BR', 'BRAZIL', 'Brazil', 'BRA'),
    (226, 'US', 'UNITED STATES', 'United States', 'USA');

-- 1: researcher, bcrypt ("secret"); 2: researcher still on SHA-1 ("secret"), unconfirmed;
-- 3: administrator without a researcher row; 4: inactive account
INSERT INTO login (log_id, log_email, log_password, log_status, log_confirmed, log_type, log_token, log_create) VALUES
    (1, ' Ana@Example.test ', '$2y$04$hb2nXpxkomk0pviQXlwd3u.YxB10OHethu/SHJ0zy7q4fC3EA34dq', 'A', 'Y', 'R', '', '2025-03-10 12:00:00'),
    (2, 'bruno@example.test', 'e5e9fa1ba31ecd1ae84f75caaa474f3a663f05f4', 'A', 'N', 'R', '', '2025-04-01 09:30:00'),
    (3, 'admin@example.test', '$2y$04$hb2nXpxkomk0pviQXlwd3u.YxB10OHethu/SHJ0zy7q4fC3EA34dq', 'A', 'Y', 'A', '', '2025-01-01 00:00:00'),
    (4, 'old@example.test', '$2y$04$hb2nXpxkomk0pviQXlwd3u.YxB10OHethu/SHJ0zy7q4fC3EA34dq', 'I', 'Y', 'R', '', '2025-01-02 00:00:00');

INSERT INTO researcher (res_id, res_name, res_photo, res_institution, res_purpose, res_academic, fk_country_cou_id, fk_login_log_id) VALUES
    (1, 'Ana Lima', 'ana.png', 'UNAERP', 'Air filtration', 'PhD student', 30, 1),
    (2, 'Bruno', '', 'IFSP', 'Masks', 'MSc', 226, 2),
    (4, 'Old account', NULL, 'X', 'Y', 'Z', NULL, 4);

-- 1: complete (image analysed, thickness 0.012 mm = 12 µm, df 0.30 µm): the v2 EN 1822 reference case
-- 2: never analysed, no filter simulation: no thickness, file missing on disk
-- 3: porosity outside 0-100 % (bad v1 value)
-- 4: owner does not exist
INSERT INTO research (ree_id, ree_name, ree_description, ree_file, ree_flow, ree_voltage, ree_distance, ree_create, fk_login_log_id) VALUES
    (1, 'PAN 10 %', 'First membrane', 'aaa111.jpg', 0.03, 20.00, 15.00, '2025-05-01 10:00:00', 1),
    (2, 'PAN 12 %', '', 'missing.jpg', 0.00, 25.00, 10.00, '2025-05-02 10:00:00', 1),
    (3, 'PVDF', 'x', 'ccc333.jpg', 1.00, 24.00, 24.00, '2025-05-03 10:00:00', 2),
    (4, 'Orphan', 'x', 'ddd444.jpg', 1.00, 24.00, 24.00, '2025-05-04 10:00:00', 99);

INSERT INTO research_results (rre_id, rre_dissimilarity, rre_correlation, rre_energy, rre_homogeneity, rre_porosity, fk_research_ree_id) VALUES
    (1, '36.94458019574228', '0.48645276999661125', '0.007428548554765485', '0.04960633935593018', '84.1', 1),
    (2, '24.687316175153825', '0.20016498358918153', '0.01258407162938385', '0.04464687143384338', '150', 3);

-- two simulations for research 1: the latest one (fis_id 2) is the one imported
INSERT INTO filter_simulation (fis_id, fis_material, fis_thickness, fis_diameter, fis_porosity, fis_temperature, fis_pressure, fis_velocity, fis_area, fis_density, fis_size_min, fis_size_max, fis_concentration, fis_class, fk_research_ree_id) VALUES
    (1, 'PAN', '1', '50', '0.841', '25', '715', '0.053', '0.02', '912', '0.01', '100', '100', '100', 1),
    (2, 'PAN', '0.012', '0.30', '0.841', '25', '715', '0.053', '0.02', '912', '0.01', '10', '100', '100', 1),
    (3, 'PVDF', '0.024', '0.471', '0.8618', '25', '760', '0.94', '0.08', '1', '0.02', '0.85', '2.7', '100', 3);
