-- Standard test scenarios (specification v2.1, section 4). Same values as
-- src/Model/Presets.php; PresetSeedTest keeps the two in step.

INSERT INTO aerosol_presets
    (code, name, standard, aerosol, efficiency_basis, velocity_cm_s, particle_density_kg_m3, cmd_um, gsd, mono, dmin_um, dmax_um, sort_order)
VALUES
    ('en1822', 'EN 1822 / ISO 29463 flat medium', 'EN 1822-3, ISO 29463-3', 'DEHS', 'Particle count at the MPPS', 5.3, 912, 0.2, 1.5, 1, 0.01, 10, 1),
    ('niosh_n', 'NIOSH 42 CFR 84, N series', '42 CFR 84.174', 'NaCl', 'Photometer (close to mass)', 14.2, 2165, 0.075, 1.86, 0, 0.01, 10, 2),
    ('niosh_rp', 'NIOSH 42 CFR 84, R and P series', '42 CFR 84.174', 'DOP', 'Photometer (close to mass)', 14.2, 985, 0.185, 1.60, 0, 0.01, 10, 3);

INSERT INTO air_presets (code, name, temperature_c, pressure_mmhg, sort_order)
VALUES
    ('ribeirao_preto', 'Ribeirão Preto', 25, 715, 1),
    ('sea_level', 'Sea level', 25, 760, 2);
