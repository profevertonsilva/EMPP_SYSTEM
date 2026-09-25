# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

Researchers who produce electrospun (nanofiber) membranes and want to estimate membrane properties without extra lab characterisation. They hold a SEM micrograph of a sample and know the electrospinning parameters it was made with (syringe flow rate, applied voltage, needle–collector distance). Access is free: any researcher can create an account and use the tool.

Administrators (the research team) oversee researchers, all studies, and export the collected image dataset for model retraining.

## Product Purpose

EMPP (Electrospun Membrane Property Predictor) turns a SEM image of an electrospun membrane plus its process parameters into:

1. Haralick texture features of the image (dissimilarity, correlation, energy, homogeneity);
2. a predicted membrane porosity, from a machine-learning model fed with the features and the process parameters;
3. a filtration performance simulation of the membrane (grade and overall efficiency, pressure drop, quality factor, MPPS, outlet concentration), from physical models.

Success: a researcher goes from micrograph to a porosity estimate and a filtration simulation in one session, and every study also grows the image dataset used to improve the model.

## Positioning

A single path from a SEM micrograph to filtration performance, built on doctoral research: image texture analysis, a trained porosity model, and classical single-fibre filtration theory in one tool, free to use.

## Operating Context

- Input: a SEM micrograph (JPG, PNG or WEBP, up to 5 MB), cropped in the tool to remove the microscope's information bar, plus flow rate (mL/min), voltage (kV) and distance (cm).
- Output per study: texture features, predicted porosity (%), optional filtration simulation with charts and reference ranges (N95, FFP2/FFP3, HEPA, WHO PM2.5).
- Studies are private to their owner; administrators can see all of them.
- The tool's interface is in English.

## Capabilities and Constraints

- Existing PHP application (custom MVC). The public site lives at `/`; the tool starts at `/sign-in` and `/sign-up`.
- The analysis APIs (Haralick, porosity) are external services reached through a server proxy.
- Predicted porosity is a model estimate, not a measurement; the dataset export keeps the two apart.
- Undecided: institution/programme, supervisor and publications to cite (confirmed to exist, details not yet provided).
- Privacy policy and terms of use text are still placeholders in the sign-up form.

## Brand Commitments

- Name: EMPP — Electrospun Membrane Property Predictor. Logos in `resources/img/` (`logo_empp_b.png`, `logo_empp_w.png`, `icone_empp.png`) and a nanofiber texture in `resources/img/fibers.svg`.
- Credit: doctoral research by Everton Rafael da Silva.
- Public site language: English.

## Evidence on Hand

- The working tool and its workflow (the four-step path above).
- Real SEM micrographs exist in the upload folder but belong to users: do not publish them without consent.
- Institution, programme, supervisor and publications: confirmed to exist, not yet supplied. Do not fabricate names, affiliations, DOIs, user counts, accuracy figures or testimonials.

## Product Principles

1. Truth over polish: every number shown is traceable to a model or a formula, and estimates are labelled as estimates.
2. One path from image to performance: each step feeds the next.
3. Free and open to researchers; getting started costs one sign-up.
4. Every study also serves science: the collected images improve the model.
