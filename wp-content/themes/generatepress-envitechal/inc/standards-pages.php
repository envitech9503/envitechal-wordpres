<?php
/**
 * Environmental quality standards reference pages: PEQS and NEQS.
 *
 * Built on the same structure as the SEQS page, which is the site's largest
 * source of organic clicks. Each page is driven by a configuration array so
 * the limit tables, FAQs, schema and links live in one place.
 *
 * Sources (transcribed from the official notifications, retained in the
 * project record):
 * - PEQS: Punjab Gazette (Extraordinary) 15-08-2016, Notification 120 of
 *   2016, SO(G)/EPD/7-26/2013 dated 05-08-2016, for Municipal and Liquid
 *   Industrial Effluents, Drinking Water, Ambient Air, Noise and Industrial
 *   Gaseous Emissions. https://epd.punjab.gov.pk/peqs
 * - NEQS: S.R.O. 549(I)/2000 (Revised NEQS for Municipal and Liquid
 *   Industrial Effluents, and Industrial Gaseous Emissions); Gazette of
 *   Pakistan (Extraordinary) 26-11-2010, S.R.O. 1062(I)/2010 Ambient Air,
 *   S.R.O. 1063(I)/2010 Drinking Water, S.R.O. 1064(I)/2010 Noise.
 */

if (!defined('ABSPATH')) {
    exit;
}

function eta_modern_standards_slugs()
{
    return ['punjab-environmental-quality-standards-peqs', 'national-environmental-quality-standards-neqs'];
}

function eta_modern_is_standards_page()
{
    return is_page(eta_modern_standards_slugs());
}

function eta_modern_standards_current_slug()
{
    foreach (eta_modern_standards_slugs() as $slug) {
        if (is_page($slug)) {
            return $slug;
        }
    }
    return '';
}

/**
 * Shared tables. PEQS adopted the 2010 national ambient air, drinking water
 * and noise standards verbatim, and the 2000 national effluent and gaseous
 * emission standards with the sea-discharge column removed, so the data is
 * defined once and referenced by both pages.
 */
function eta_modern_standards_effluent_rows($with_sea)
{
    $rows = [
        ['Temperature or temperature increase', '≤ 3 °C', '≤ 3 °C', '≤ 3 °C'],
        ['pH value', '6 to 9', '6 to 9', '6 to 9'],
        ['BOD5 at 20 °C (1)', '80', '250', '80 (**)'],
        ['COD (1)', '150', '400', '400'],
        ['Total suspended solids (TSS)', '200', '400', '200'],
        ['Total dissolved solids (TDS)', '3,500', '3,500', '3,500'],
        ['Grease and oil', '10', '10', '10'],
        ['Phenolic compounds (as phenol)', '0.1', '0.3', '0.3'],
        ['Chloride (as Cl⁻)', '1,000', '1,000', 'SC (***)'],
        ['Fluoride (as F⁻)', '10', '10', '10'],
        ['Cyanide (as CN⁻) total', '1.0', '1.0', '1.0'],
        ['Anionic detergents (as MBAS) (2)', '20', '20', '20'],
        ['Sulphate (SO₄²⁻)', '600', '1,000', 'SC (***)'],
        ['Sulphide (S²⁻)', '1.0', '1.0', '1.0'],
        ['Ammonia (NH₃)', '40', '40', '40'],
        ['Pesticides (3)', '0.15', '0.15', '0.15'],
        ['Cadmium (4)', '0.1', '0.1', '0.1'],
        ['Chromium, trivalent and hexavalent (4)', '1.0', '1.0', '1.0'],
        ['Copper (4)', '1.0', '1.0', '1.0'],
        ['Lead (4)', '0.5', '0.5', '0.5'],
        ['Mercury (4)', '0.01', '0.01', '0.01'],
        ['Selenium (4)', '0.5', '0.5', '0.5'],
        ['Nickel (4)', '1.0', '1.0', '1.0'],
        ['Silver (4)', '1.0', '1.0', '1.0'],
        ['Total toxic metals', '2.0', '2.0', '2.0'],
        ['Zinc', '5.0', '5.0', '5.0'],
        ['Arsenic (4)', '1.0', '1.0', '1.0'],
        ['Barium (4)', '1.5', '1.5', '1.5'],
        ['Iron', '8.0', '8.0', '8.0'],
        ['Manganese', '1.5', '1.5', '1.5'],
        ['Boron (4)', '6.0', '6.0', '6.0'],
        ['Chlorine (Cl₂)', '1.0', '1.0', '1.0'],
    ];
    if ($with_sea) {
        return $rows;
    }
    return array_map(function ($r) {
        return [$r[0], $r[1], $r[2]];
    }, $rows);
}

function eta_modern_standards_effluent_notes()
{
    return [
        '(1) Assumes a minimum dilution of 1:10 on discharge; a lower ratio attracts progressively stricter limits set by the agency.',
        '(2) Methylene blue active substances, assuming the surfactant is biodegradable.',
        '(3) Pesticides include herbicides, fungicides and insecticides.',
        '(4) Subject to total toxic metals: the combined discharge must not exceed the limit at "Total toxic metals".',
        'Temperature: the effluent must not raise the receiving body by more than 3 °C at the edge of the mixing zone, or at 100 m from the outfall where no zone is defined.',
        'Dilution with fresh water to meet a limit is not permitted. The concentration of pollutants already present in the intake water is subtracted when calculating compliance.',
    ];
}

function eta_modern_standards_drinking_water_rows()
{
    return [
        ['E. coli or thermotolerant coliform', 'Not detectable in any 100 mL sample', 'Not detectable in any 100 mL sample'],
        ['Total coliform (treated water)', 'Not detectable in any 100 mL sample', 'Not detectable in any 100 mL sample'],
        ['Colour', '15 TCU or less', '15 TCU or less'],
        ['Turbidity', 'Below 5 NTU', 'Below 5 NTU'],
        ['Total hardness as CaCO₃', 'Below 500 mg/L', 'No guideline value'],
        ['Total dissolved solids', 'Below 1,000 mg/L', 'Below 1,000 mg/L'],
        ['pH', '6.5 to 8.5', '6.5 to 8.5'],
        ['Aluminium', '0.2 mg/L or less', '0.2 mg/L'],
        ['Antimony', '0.005 mg/L or less (P)', '0.02 mg/L'],
        ['Arsenic', '0.05 mg/L or less (P)', '0.01 mg/L'],
        ['Barium', '0.7 mg/L', '0.7 mg/L'],
        ['Boron', '0.3 mg/L', '0.3 mg/L'],
        ['Cadmium', '0.01 mg/L', '0.003 mg/L'],
        ['Chloride', 'Below 250 mg/L', '250 mg/L'],
        ['Chromium', '0.05 mg/L or less', '0.05 mg/L'],
        ['Copper', '2 mg/L', '2 mg/L'],
        ['Cyanide', '0.05 mg/L or less', '0.07 mg/L'],
        ['Fluoride (*)', '1.5 mg/L or less', '1.5 mg/L'],
        ['Lead', '0.05 mg/L or less', '0.01 mg/L'],
        ['Manganese', '0.5 mg/L or less', '0.5 mg/L'],
        ['Mercury', '0.001 mg/L or less', '0.001 mg/L'],
        ['Nickel', '0.02 mg/L or less', '0.02 mg/L'],
        ['Nitrate as NO₃ (*)', '50 mg/L or less', '50 mg/L'],
        ['Nitrite as NO₂ (*)', '3 mg/L or less (P)', '3 mg/L'],
        ['Selenium', '0.01 mg/L (P)', '0.01 mg/L'],
        ['Residual chlorine', '0.2 to 0.5 mg/L at consumer end; 0.5 to 1.5 mg/L at source', 'No single value'],
        ['Zinc', '5.0 mg/L', '3 mg/L'],
        ['Alpha emitters', '0.1 Bq/L', '0.1 Bq/L'],
        ['Beta emitters', '1 Bq/L', '1 Bq/L'],
    ];
}

function eta_modern_standards_drinking_water_notes()
{
    return [
        '(*) Priority health-related inorganic constituents that need regular monitoring.',
        '(P) Provisional value in the notification.',
        'Pesticides are referred to PSQCA standard 4639-2004 (Table 3, serial 20 to 58). The WHO guideline for polynuclear aromatic hydrocarbons is 0.01 µg/L by GC-MS.',
        'The notification notes that the Pakistan values for arsenic, lead, cadmium and zinc are set above WHO guidelines because of existing treatment and pipework; bottled water must meet the WHO limits for those four parameters.',
    ];
}

function eta_modern_standards_ambient_air_rows()
{
    return [
        ['Sulphur dioxide (SO₂)', 'Annual average (*)', '80 µg/m³', 'Ultraviolet fluorescence'],
        ['', '24 hours (**)', '120 µg/m³', ''],
        ['Oxides of nitrogen as NO', 'Annual average (*)', '40 µg/m³', 'Gas-phase chemiluminescence'],
        ['', '24 hours (**)', '40 µg/m³', ''],
        ['Oxides of nitrogen as NO₂', 'Annual average (*)', '40 µg/m³', 'Gas-phase chemiluminescence'],
        ['', '24 hours (**)', '80 µg/m³', ''],
        ['Ozone (O₃)', '1 hour', '130 µg/m³', 'Non-dispersive UV absorption'],
        ['Suspended particulate matter (SPM)', 'Annual average (*)', '360 µg/m³', 'High-volume sampling, flow not below 1.1 m³/min'],
        ['', '24 hours (**)', '500 µg/m³', ''],
        ['Respirable particulate matter PM10', 'Annual average (*)', '120 µg/m³', 'Beta-ray absorption preferred'],
        ['', '24 hours (**)', '150 µg/m³', ''],
        ['Respirable particulate matter PM2.5', 'Annual average (*)', '15 µg/m³', 'Beta-ray absorption preferred'],
        ['', '24 hours (**)', '35 µg/m³', ''],
        ['', '1 hour', '15 µg/m³', ''],
        ['Lead (Pb)', 'Annual average (*)', '1 µg/m³', 'AAS after sampling on EPM 2000 or equivalent filter'],
        ['', '24 hours (**)', '1.5 µg/m³', ''],
        ['Carbon monoxide (CO)', '8 hours (**)', '5 mg/m³', 'Non-dispersive infrared (NDIR)'],
        ['', '1 hour', '10 mg/m³', ''],
    ];
}

function eta_modern_standards_ambient_air_notes()
{
    return [
        '(*) Annual arithmetic mean of at least 104 measurements a year, taken twice a week as 24-hourly values at uniform intervals.',
        '(**) 24-hourly and 8-hourly values must be met 98% of the time in a year; they may be exceeded 2% of the time but not on two consecutive days.',
        'Values shown are those effective from 1 January 2013 (national) and adopted by Punjab in 2016.',
    ];
}

function eta_modern_standards_noise_rows()
{
    return [
        ['Residential area (A)', '55', '45'],
        ['Commercial area (B)', '65', '55'],
        ['Industrial area (C)', '75', '65'],
        ['Silence zone (D)', '50', '45'],
    ];
}

function eta_modern_standards_noise_notes()
{
    return [
        'Limits in dB(A) Leq, the time-weighted average sound level on the A scale. Day time is 06:00 to 22:00; night time is 22:00 to 06:00.',
        'A silence zone is declared by the competent authority and includes at least 100 m around hospitals, educational institutions and courts. Mixed areas may be assigned to one of the four categories by the authority.',
        'The values shown are the second-stage limits (effective 1 July 2012 nationally, 1 July 2013 in Punjab). The earlier first-stage limits were 10 dB(A) higher in most categories.',
    ];
}

function eta_modern_standards_emissions_rows()
{
    return [
        ['Smoke', 'Smoke opacity not to exceed', '40% or 2 on the Ringelmann scale'],
        ['Particulate matter (1)', 'Boilers and furnaces, oil fired', '300'],
        ['', 'Boilers and furnaces, coal fired', '500'],
        ['', 'Cement kilns', '300'],
        ['', 'Grinding, crushing, clinker coolers, metallurgical processes, converters, blast furnaces and cupolas', '500'],
        ['Hydrogen chloride', 'Any', '400'],
        ['Chlorine', 'Any', '150'],
        ['Hydrogen fluoride', 'Any', '150'],
        ['Hydrogen sulphide', 'Any', '10'],
        ['Sulphur oxides (2)(3)', 'Sulphuric acid and sulphonic acid plants', '5,000'],
        ['', 'Other plants, except power plants operating on oil and coal', '1,700'],
        ['Carbon monoxide', 'Any', '800'],
        ['Lead', 'Any', '50'],
        ['Mercury', 'Any', '10'],
        ['Cadmium', 'Any', '20'],
        ['Arsenic', 'Any', '20'],
        ['Copper', 'Any', '50'],
        ['Antimony', 'Any', '20'],
        ['Zinc', 'Any', '200'],
        ['Oxides of nitrogen (3)', 'Nitric acid manufacturing unit', '3,000'],
        ['', 'Other plants (except oil and coal power plants), gas fired', '400'],
        ['', 'Other plants (except oil and coal power plants), oil fired', '600'],
        ['', 'Other plants (except oil and coal power plants), coal fired', '1,200'],
    ];
}

function eta_modern_standards_emissions_notes()
{
    return [
        'Values in mg/Nm³ unless stated. (1) Assumes particulate size of 10 micron or more. (2) Based on 1% sulphur in fuel oil; higher sulphur content pro-rates the limit.',
        '(3) Power plants operating on oil and coal must also meet separate SO₂ and NOx criteria tied to background air quality, set out in the notification.',
        'Dilution of emissions with excess air before discharge is not permitted.',
    ];
}

function eta_modern_standards_pages()
{
    $labs = ['Karachi' => '/karachi-environmental-lab/', 'Lahore' => '/lahore-environmental-lab/'];

    return [
        'punjab-environmental-quality-standards-peqs' => [
            'short' => 'PEQS',
            'long' => 'Punjab Environmental Quality Standards',
            'title' => 'PEQS Limits and Compliance Guide | Envi Tech AL',
            'h1' => 'Punjab Environmental Quality Standards (PEQS): Limits, Parameters and Compliance',
            'meta' => 'PEQS stands for Punjab Environmental Quality Standards. The 2016 limits for effluent, drinking water, ambient air, noise and emissions, and how to test.',
            'definition' => 'PEQS stands for Punjab Environmental Quality Standards.',
            'definition_rest' => 'They are the legally binding limits, notified under the Punjab Environmental Protection Act 1997 by the Environment Protection Department and enforced by the Punjab Environmental Protection Agency (Punjab EPA), for what facilities in Punjab may discharge to water, emit to air and generate as noise, together with standards for ambient air and drinking water.',
            'intro' => 'This guide presents the PEQS limit tables from the official notifications of 05-08-2016 (Punjab Gazette, 15-08-2016) and shows how facilities in Lahore and across Punjab demonstrate compliance to the Punjab EPA.',
            'reviewed' => 'Last reviewed: October 2026',
            'agency' => 'Punjab EPA',
            'act' => 'Punjab Environmental Protection Act 1997',
            'scope' => ['Effluent', 'Drinking water', 'Ambient air', 'Noise', 'Gaseous emissions'],
            'what_title' => 'What PEQS means, and why it binds your facility',
            'what' => 'After the 18th Constitutional Amendment devolved environmental regulation to the provinces, Punjab notified its own standards in 2016 under section 4 of the Punjab Environmental Protection Act 1997. They adopt the national framework (NEQS) with Punjab-specific notifications for effluent, drinking water, ambient air, noise, motor vehicles, biomedical waste and industrial gaseous emissions. If your facility holds, or needs, an approval, NOC or Environmental Management Plan condition from the Punjab EPA, PEQS limits are the numbers your Environmental Monitoring Reports are judged against. Exceeding them exposes the facility to action under the Act and puts renewals at risk.',
            'who_title' => 'Who must comply',
            'who' => 'PEQS obligations apply across textile and garment units, leather and tanneries, food and beverage plants, pharmaceutical manufacturers, chemical, paper and cement works, steel and foundries, hospitals, hotels, housing schemes, construction projects and commercial facilities in Lahore, Faisalabad, Sialkot, Gujranwala, Multan, Rawalpindi and the rest of Punjab: in practice, any operation that discharges effluent, runs generators or boilers, emits to air, or operates under an EMP or EMR condition. Facilities in Sindh answer instead to SEQS and the Sindh EPA; the two frameworks are closely aligned but not identical, so test against the standard your approval cites.',
            'tables' => [
                [
                    'id' => 'peqs-effluent',
                    'eyebrow' => 'Effluent limits table',
                    'title' => 'PEQS for municipal and liquid industrial effluents',
                    'lead' => 'All 32 parameters from the notification. Values in mg/L unless stated. Punjab notifies two discharge destinations: inland waters, and sewage treatment.',
                    'columns' => ['Parameter', 'Into inland waters', 'Into sewage treatment'],
                    'rows' => eta_modern_standards_effluent_rows(false),
                    'notes' => array_merge(eta_modern_standards_effluent_notes(), ['(**) For sewage treatment the BOD5 limit applies only where treatment is operational and achieves 80 mg/L.']),
                ],
                [
                    'id' => 'peqs-drinking-water',
                    'eyebrow' => 'Drinking water',
                    'title' => 'PEQS for drinking water',
                    'lead' => 'Bacteriological, physical, inorganic and radioactive parameters with the WHO guideline value alongside, as printed in the notification.',
                    'columns' => ['Parameter', 'PEQS value', 'WHO guideline'],
                    'rows' => eta_modern_standards_drinking_water_rows(),
                    'notes' => eta_modern_standards_drinking_water_notes(),
                ],
                [
                    'id' => 'peqs-ambient-air',
                    'eyebrow' => 'Ambient air',
                    'title' => 'PEQS for ambient air',
                    'lead' => 'Concentration limits by averaging period, with the prescribed measurement method.',
                    'columns' => ['Pollutant', 'Averaging period', 'Limit', 'Method'],
                    'rows' => eta_modern_standards_ambient_air_rows(),
                    'notes' => eta_modern_standards_ambient_air_notes(),
                ],
                [
                    'id' => 'peqs-noise',
                    'eyebrow' => 'Noise',
                    'title' => 'PEQS for noise',
                    'lead' => 'Limits by zone, effective from 1 July 2013.',
                    'columns' => ['Zone', 'Day time, dB(A) Leq', 'Night time, dB(A) Leq'],
                    'rows' => eta_modern_standards_noise_rows(),
                    'notes' => eta_modern_standards_noise_notes(),
                ],
                [
                    'id' => 'peqs-emissions',
                    'eyebrow' => 'Stack and gaseous emissions',
                    'title' => 'PEQS for industrial gaseous emissions',
                    'lead' => 'Source-specific limits for boilers, furnaces, kilns and process stacks.',
                    'columns' => ['Parameter', 'Source of emission', 'Standard (mg/Nm³)'],
                    'rows' => eta_modern_standards_emissions_rows(),
                    'notes' => eta_modern_standards_emissions_notes(),
                ],
            ],
            'process' => [
                ['Know your conditions.', 'Your Punjab EPA approval, NOC or EMP specifies which PEQS notifications apply, which points are sampled and how often.'],
                ['Monitor and test.', 'Samples and field measurements are taken at the required frequency. Envi Tech AL confirms the laboratory, parameters, methods and PNAC scope status against the applicable limits before testing. The Lahore laboratory is listed on the Punjab EPA record of environmental laboratories.'],
                ['Report.', 'Results are compiled into the Environmental Monitoring Report submitted to the Punjab EPA, stating the result against the PEQS limit parameter by parameter.'],
                ['Act on exceedances.', 'Where a result exceeds a limit, corrective action may include process adjustment, treatment plant optimisation or retesting, documented for the next report.'],
            ],
            'downloads' => [
                ['Effluents (official PDF)', 'https://epd.punjab.gov.pk/system/files/Punjab%20Environmental%20Quality%20Standards%20for%20Muncipal%20And%20Liquid%20Industrial%20Effluents%20final_0.pdf'],
                ['Drinking water (official PDF)', 'https://epd.punjab.gov.pk/system/files/NOTIFICATION_REGARDING_THE_PUNJAB_ENVIRONMENTAL_QUALITY_STANDARDS_FOR_DRINKING_WATER.pdf'],
                ['Ambient air (official PDF)', 'https://epd.punjab.gov.pk/system/files/NOTIFICATION_REGARDING_THE_PUNJAB_ENVIRONMENTAL_QUALITY_STANDARDS_FOR_AMBIENT_AIR.pdf'],
                ['Noise (official PDF)', 'https://epd.punjab.gov.pk/system/files/NOTIFICATION_REGARDING_THE_PUNJAB_ENVIRONMENTAL_QUALITY_STANDARDS_FOR_NOISE.pdf'],
                ['Industrial gaseous emissions (official PDF)', 'https://epd.punjab.gov.pk/system/files/NOTIFICATION_REGARDING_THE_PUNJAB_ENVIRONMENTAL_QUALITY_STANDARDS_FOR_INDUSTRIAL_GASEOUS_EMISSIONS%20%281%29.pdf'],
                ['All PEQS notifications (Punjab EPD)', 'https://epd.punjab.gov.pk/peqs'],
            ],
            'related' => [
                ['Lahore environmental testing lab', 'PNAC LAB-347, listed by Punjab EPA.', '/lahore-environmental-lab/'],
                ['Water testing lab in Lahore', 'Drinking water, effluent and RO testing against PEQS.', '/water-testing-lab-lahore/'],
                ['SEQS: the Sindh standards', 'The Sindh equivalent, for facilities in Karachi.', '/sindh-environmental-quality-standards-seqs/'],
                ['NEQS: the national standards', 'The framework both provinces adopted.', '/national-environmental-quality-standards-neqs/'],
                ['EMR and EMP reporting', 'How monitoring reports are prepared and submitted.', '/environmental-monitoring-report-emr-emp-sindh-epa/'],
                ['Sindh EPA vs Punjab EPA NOC', 'How the two approval routes differ.', '/sindh-epa-vs-punjab-epa-noc-lahore/'],
            ],
            'faqs' => [
                ['What does PEQS stand for?', 'PEQS stands for Punjab Environmental Quality Standards: the legally enforceable limits for effluent discharge, drinking water, ambient air, noise, motor vehicle exhaust and industrial gaseous emissions in Punjab, notified in 2016 under the Punjab Environmental Protection Act 1997 and enforced by the Punjab Environmental Protection Agency.'],
                ['What are the PEQS limits for wastewater discharge?', 'Key effluent limits are pH 6 to 9, BOD5 of 80 mg/L into inland waters (250 mg/L into sewage treatment), COD of 150 mg/L into inland waters (400 mg/L into sewage treatment), TSS of 200 mg/L (400 mg/L into sewage treatment), TDS of 3,500 mg/L, oil and grease of 10 mg/L, and metal limits such as lead 0.5 mg/L, cadmium 0.1 mg/L, chromium 1.0 mg/L and mercury 0.01 mg/L. The full 32-parameter table is on this page.'],
                ['What are the PEQS limits for drinking water?', 'E. coli must not be detectable in any 100 mL sample; TDS below 1,000 mg/L; total hardness below 500 mg/L as CaCO3; pH 6.5 to 8.5; turbidity below 5 NTU; arsenic 0.05 mg/L; nitrate 50 mg/L; fluoride 1.5 mg/L; lead 0.05 mg/L; and residual chlorine of 0.2 to 0.5 mg/L at the consumer end. The full table with WHO values alongside is on this page.'],
                ['What is the difference between PEQS, SEQS and NEQS?', 'NEQS are the national standards. After environmental regulation devolved to the provinces, Punjab notified PEQS (2016) and Sindh notified SEQS (2016), each enforceable within its province. A facility in Lahore answers to PEQS and the Punjab EPA; a facility in Karachi answers to SEQS and the Sindh EPA. The effluent, air and noise values are closely aligned, but the notifications differ in detail, so test against the standard your approval cites.'],
                ['What are the PEQS noise limits?', 'From 1 July 2013: residential areas 55 dB(A) by day and 45 at night; commercial areas 65 and 55; industrial areas 75 and 65; silence zones 50 and 45. Day time is 06:00 to 22:00.'],
                ['What are the PEQS limits for stack emissions?', 'Particulate matter is limited to 300 mg/Nm³ for oil-fired boilers and furnaces and 500 mg/Nm³ for coal-fired; sulphur oxides to 1,700 mg/Nm³ for plants other than sulphuric acid and power plants; oxides of nitrogen to 400, 600 and 1,200 mg/Nm³ for gas, oil and coal-fired plants; carbon monoxide to 800 mg/Nm³; and smoke opacity to 40% or Ringelmann 2.'],
                ['Where can I download the official PEQS notifications?', 'The Punjab Environment Protection Department publishes each PEQS notification as a PDF on epd.punjab.gov.pk. Direct links to the effluent, drinking water, ambient air, noise and gaseous emission notifications are in the download section of this page.'],
                ['How do I test my facility against PEQS?', 'Send the discharge or emission type, site location, approval conditions, required parameters and reporting purpose. Envi Tech AL confirms the laboratory, methods, PNAC scope status and the exact PEQS panel before accepting the work, and can sample on site across Lahore and Punjab.'],
            ],
            'schema_about' => 'Punjab Environmental Quality Standards',
        ],
        'national-environmental-quality-standards-neqs' => [
            'short' => 'NEQS',
            'long' => 'National Environmental Quality Standards',
            'title' => 'NEQS Limits and Compliance Guide | Envi Tech AL',
            'meta' => 'NEQS stands for National Environmental Quality Standards. Pakistan\'s limits for effluent, emissions, ambient air, drinking water and noise, with official links.',
            'h1' => 'National Environmental Quality Standards (NEQS): Limits, Parameters and Compliance',
            'definition' => 'NEQS stands for National Environmental Quality Standards.',
            'definition_rest' => 'They are Pakistan\'s federal limits, established by the Pakistan Environmental Protection Agency under the Pakistan Environmental Protection Act 1997, for municipal and industrial effluent, industrial gaseous emissions, motor vehicle exhaust, ambient air quality, drinking water and noise.',
            'intro' => 'This guide presents the NEQS limit tables from S.R.O. 549(I)/2000 (effluent and gaseous emissions) and the 2010 notifications for ambient air, drinking water and noise, and explains how NEQS relate to the provincial SEQS and PEQS that now apply in Sindh and Punjab.',
            'reviewed' => 'Last reviewed: October 2026',
            'agency' => 'Pak-EPA and the provincial EPAs',
            'act' => 'Pakistan Environmental Protection Act 1997',
            'scope' => ['Effluent', 'Gaseous emissions', 'Ambient air', 'Drinking water', 'Noise'],
            'what_title' => 'What NEQS means, and where it still applies',
            'what' => 'NEQS were first notified in 1993 and revised by S.R.O. 549(I)/2000, which set the effluent and gaseous emission limits still in use. In 2010 the Pakistan EPA added national standards for ambient air (S.R.O. 1062(I)/2010), drinking water (S.R.O. 1063(I)/2010) and noise (S.R.O. 1064(I)/2010). After the 18th Constitutional Amendment, Sindh and Punjab notified their own SEQS and PEQS, which adopt the NEQS tables almost unchanged. NEQS remain the reference standard in Islamabad Capital Territory and in provinces and territories that have not notified their own, and they are the basis every provincial standard is compared against. Many approvals, buyer codes and bank conditions still cite NEQS by name.',
            'who_title' => 'Who must comply',
            'who' => 'Any facility in Pakistan that discharges effluent, operates boilers, generators or process stacks, or affects ambient air and noise at its boundary: textile, leather, food, pharmaceutical, chemical, cement, steel, power and oil and gas operations, hospitals, hotels and construction projects. In Sindh the applicable standard is SEQS and the regulator is the Sindh EPA; in Punjab it is PEQS and the Punjab EPA. Where an approval, buyer or lender cites NEQS directly, the tables on this page are the ones to test against.',
            'tables' => [
                [
                    'id' => 'neqs-effluent',
                    'eyebrow' => 'Effluent limits table',
                    'title' => 'NEQS for municipal and liquid industrial effluents (S.R.O. 549(I)/2000)',
                    'lead' => 'All 32 parameters. Values in mg/L unless stated. NEQS notifies three discharge destinations: inland waters, sewage treatment, and the sea.',
                    'columns' => ['Parameter', 'Into inland waters', 'Into sewage treatment', 'Into the sea'],
                    'rows' => eta_modern_standards_effluent_rows(true),
                    'notes' => array_merge(eta_modern_standards_effluent_notes(), ['(**) The BOD5 value for industry discharging to the sea is 200 mg/L. (***) SC: discharge concentration at or below the sea concentration. Sea discharge limits assume the outfall is not at the shore and not within 10 miles of mangroves or other important estuaries.']),
                ],
                [
                    'id' => 'neqs-emissions',
                    'eyebrow' => 'Stack and gaseous emissions',
                    'title' => 'NEQS for industrial gaseous emissions (S.R.O. 549(I)/2000)',
                    'lead' => 'Source-specific limits for boilers, furnaces, kilns and process stacks.',
                    'columns' => ['Parameter', 'Source of emission', 'Standard (mg/Nm³)'],
                    'rows' => eta_modern_standards_emissions_rows(),
                    'notes' => eta_modern_standards_emissions_notes(),
                ],
                [
                    'id' => 'neqs-ambient-air',
                    'eyebrow' => 'Ambient air',
                    'title' => 'NEQS for ambient air (S.R.O. 1062(I)/2010)',
                    'lead' => 'Concentration limits effective from 1 January 2013, with the prescribed measurement method.',
                    'columns' => ['Pollutant', 'Averaging period', 'Limit', 'Method'],
                    'rows' => eta_modern_standards_ambient_air_rows(),
                    'notes' => eta_modern_standards_ambient_air_notes(),
                ],
                [
                    'id' => 'neqs-drinking-water',
                    'eyebrow' => 'Drinking water',
                    'title' => 'National Standards for Drinking Water Quality (S.R.O. 1063(I)/2010)',
                    'lead' => 'Bacteriological, physical, inorganic and radioactive parameters with the WHO guideline value alongside, as printed in the notification.',
                    'columns' => ['Parameter', 'Pakistan standard', 'WHO guideline'],
                    'rows' => eta_modern_standards_drinking_water_rows(),
                    'notes' => eta_modern_standards_drinking_water_notes(),
                ],
                [
                    'id' => 'neqs-noise',
                    'eyebrow' => 'Noise',
                    'title' => 'NEQS for noise (S.R.O. 1064(I)/2010)',
                    'lead' => 'Limits by zone, effective from 1 July 2012.',
                    'columns' => ['Zone', 'Day time, dB(A) Leq', 'Night time, dB(A) Leq'],
                    'rows' => eta_modern_standards_noise_rows(),
                    'notes' => eta_modern_standards_noise_notes(),
                ],
            ],
            'process' => [
                ['Identify the applicable standard.', 'Check whether your approval, buyer or lender cites NEQS, SEQS or PEQS. In Sindh and Punjab the provincial standard governs; elsewhere, and in federal territory, NEQS apply.'],
                ['Monitor and test.', 'Samples and field measurements are taken at the required points and frequency. Envi Tech AL confirms the laboratory, parameters, methods and PNAC scope status against the applicable limits before testing, from the Karachi (LAB-285) or Lahore (LAB-347) laboratory.'],
                ['Report.', 'Results are compiled into the Environmental Monitoring Report, stating each result against the limit it is compared with.'],
                ['Act on exceedances.', 'Where a result exceeds a limit, corrective action may include process adjustment, treatment plant optimisation or retesting, documented for the next report.'],
            ],
            'downloads' => [
                ['NEQS 2010: ambient air, drinking water and noise (Gazette PDF)', 'https://faolex.fao.org/docs/pdf/pak206474.pdf'],
                ['All NEQS notifications (Ministry of Climate Change)', 'https://mocc.gov.pk/Detail/MDUzMDI1OGItYWYzZC00NzQ0LTlhZWItZjYzY2RkOTkyZGVh'],
                ['Sindh and national laws on the Envi Tech AL downloads page', '/downloads/'],
            ],
            'related' => [
                ['SEQS: the Sindh standards', 'What applies in Karachi and Sindh.', '/sindh-environmental-quality-standards-seqs/'],
                ['PEQS: the Punjab standards', 'What applies in Lahore and Punjab.', '/punjab-environmental-quality-standards-peqs/'],
                ['Karachi environmental testing lab', 'PNAC LAB-285, Sindh EPA certified.', '/karachi-environmental-lab/'],
                ['Lahore environmental testing lab', 'PNAC LAB-347, listed by Punjab EPA.', '/lahore-environmental-lab/'],
                ['Wastewater and ETP effluent testing', 'Parameters, sampling and reporting against NEQS limits.', '/wastewater-etp-effluent-testing-seqs-neqs/'],
                ['How to read a water test report', 'Units, detection limits and standard comparisons.', '/how-to-read-water-test-report-peqs-who/'],
            ],
            'faqs' => [
                ['What does NEQS stand for?', 'NEQS stands for National Environmental Quality Standards: Pakistan\'s federal limits for municipal and industrial effluent, industrial gaseous emissions, motor vehicle exhaust, ambient air, drinking water and noise, established by the Pakistan Environmental Protection Agency under the Pakistan Environmental Protection Act 1997.'],
                ['What are the NEQS limits for wastewater?', 'Key effluent limits are pH 6 to 9, BOD5 of 80 mg/L into inland waters and the sea (250 mg/L into sewage treatment), COD of 150 mg/L into inland waters (400 mg/L otherwise), TSS of 200 mg/L (400 into sewage treatment), TDS of 3,500 mg/L, oil and grease of 10 mg/L, and metals such as lead 0.5 mg/L, cadmium 0.1 mg/L, chromium 1.0 mg/L and mercury 0.01 mg/L. The full 32-parameter table is on this page.'],
                ['What are the NEQS for drinking water?', 'The National Standards for Drinking Water Quality (2010) require E. coli to be undetectable in any 100 mL sample, TDS below 1,000 mg/L, hardness below 500 mg/L, pH 6.5 to 8.5, turbidity below 5 NTU, arsenic 0.05 mg/L, nitrate 50 mg/L, fluoride 1.5 mg/L, lead 0.05 mg/L and residual chlorine of 0.2 to 0.5 mg/L at the consumer end.'],
                ['Are NEQS still in force after the 18th Amendment?', 'Yes, as the national reference. Sindh and Punjab have notified SEQS and PEQS, which govern within those provinces and adopt the NEQS tables with small differences. NEQS continue to apply in Islamabad Capital Territory and wherever a province or territory has not notified its own standards, and they remain the baseline cited by many approvals, buyer codes and lenders.'],
                ['What is the difference between NEQS, SEQS and PEQS?', 'NEQS are national; SEQS are Sindh\'s and PEQS are Punjab\'s provincial versions. The limit values are largely identical. The main structural difference is that NEQS and SEQS include a sea-discharge column for effluent, while PEQS notifies only inland waters and sewage treatment. Always test against the standard named in your approval.'],
                ['What are the NEQS noise limits?', 'From 1 July 2012: residential areas 55 dB(A) by day and 45 at night; commercial 65 and 55; industrial 75 and 65; silence zones 50 and 45. Day time is 06:00 to 22:00.'],
                ['Where can I download the NEQS notifications?', 'The Ministry of Climate Change lists the NEQS notifications on its Acts, Rules and Regulations page, and the 2010 Gazette for ambient air, drinking water and noise is available as a PDF. Links are in the download section of this page.'],
                ['How do I test my facility against NEQS?', 'Send the discharge or emission type, site location, approval conditions, required parameters and reporting purpose. Envi Tech AL confirms the laboratory, methods, PNAC scope status and the exact parameter panel before accepting the work, from its Karachi or Lahore laboratory.'],
            ],
            'schema_about' => 'National Environmental Quality Standards',
        ],
    ];
}

function eta_modern_standards_config($slug = '')
{
    $slug = $slug ?: eta_modern_standards_current_slug();
    $pages = eta_modern_standards_pages();
    return $pages[$slug] ?? null;
}

function eta_modern_standards_title($slug = '')
{
    $c = eta_modern_standards_config($slug);
    return $c ? $c['title'] : '';
}

function eta_modern_standards_meta_description($slug = '')
{
    $c = eta_modern_standards_config($slug);
    return $c ? $c['meta'] : '';
}

function eta_modern_standards_faqs($slug = '')
{
    $c = eta_modern_standards_config($slug);
    return $c ? $c['faqs'] : [];
}

/**
 * Schema nodes for the @graph: Article plus FAQPage.
 */
function eta_modern_standards_schema_nodes($description)
{
    $c = eta_modern_standards_config();
    if (!$c) {
        return [];
    }
    $nodes = [];
    $nodes['eta-standards-article'] = [
        '@type' => 'Article',
        '@id' => get_permalink() . '#article',
        'headline' => $c['h1'],
        'description' => $description,
        'url' => get_permalink(),
        'datePublished' => get_the_date('c'),
        'dateModified' => get_the_modified_date('c'),
        'author' => ['@id' => home_url('/#organization')],
        'publisher' => ['@id' => home_url('/#organization')],
        'mainEntityOfPage' => get_permalink(),
        'image' => eta_modern_default_share_image(),
        'about' => ['@type' => 'Thing', 'name' => $c['schema_about']],
        'inLanguage' => 'en-GB',
    ];
    $faq = eta_modern_schema_faq_page($c['faqs'], $c['short'] . ' FAQ');
    if ($faq) {
        $nodes['eta-standards-faq'] = $faq;
    }
    return $nodes;
}

/**
 * Create the pages once, the same way the SEQS page is created.
 */
add_action('init', function () {
    if (wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST)) {
        return;
    }
    foreach (eta_modern_standards_pages() as $slug => $c) {
        if (!get_page_by_path($slug)) {
            wp_insert_post([
                'post_type' => 'page',
                'post_status' => 'publish',
                'post_title' => $c['h1'],
                'post_name' => $slug,
                'post_excerpt' => $c['meta'],
            ]);
        }
    }
}, 9);

function eta_modern_render_standards_table($table)
{
    $cols = count($table['columns']);
    ?>
    <section id="<?php echo esc_attr($table['id']); ?>" class="eta-band eta-seqs-limits" aria-label="<?php echo esc_attr($table['title']); ?>">
        <div class="eta-shell">
            <?php eta_modern_section_title($table['eyebrow'], $table['title'], $table['lead']); ?>
            <div class="eta-responsive-table-wrap">
                <table class="eta-responsive-table eta-seqs-table">
                    <caption class="screen-reader-text"><?php echo esc_html($table['title']); ?></caption>
                    <thead>
                        <tr>
                            <?php foreach ($table['columns'] as $col) : ?>
                                <th scope="col"><?php echo esc_html($col); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($table['rows'] as $row) : ?>
                            <tr>
                                <?php for ($i = 0; $i < $cols; $i++) : ?>
                                    <?php if ($i === 0) : ?>
                                        <th scope="row"><?php echo esc_html($row[0]); ?></th>
                                    <?php else : ?>
                                        <td><?php echo esc_html($row[$i] ?? ''); ?></td>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php foreach ($table['notes'] as $note) : ?>
                <p class="eta-seqs-note"><?php echo esc_html($note); ?></p>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
}

function eta_modern_render_standards_page()
{
    $c = eta_modern_standards_config();
    if (!$c) {
        return;
    }
    $id = sanitize_title($c['short']);
    ?>
    <section class="eta-seqs-hero" aria-labelledby="eta-<?php echo esc_attr($id); ?>-title">
        <div class="eta-shell eta-seqs-hero-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('Regulatory Compliance Guide', 'envi-tech-al-modern'); ?></p>
                <h1 id="eta-<?php echo esc_attr($id); ?>-title"><?php echo esc_html($c['h1']); ?></h1>
                <p class="eta-seqs-definition"><strong><?php echo esc_html($c['definition']); ?></strong> <?php echo esc_html($c['definition_rest']); ?></p>
                <p><?php echo esc_html($c['intro']); ?></p>
                <p class="eta-seqs-reviewed"><?php echo esc_html($c['reviewed']); ?></p>
                <div class="eta-actions">
                    <a class="eta-button" href="#<?php echo esc_attr($c['tables'][0]['id']); ?>"><?php echo esc_html(sprintf(__('See the %s limit tables', 'envi-tech-al-modern'), $c['short'])); ?></a>
                    <a class="eta-button eta-button-secondary" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php echo esc_html(sprintf(__('Need %s testing? Request a quotation', 'envi-tech-al-modern'), $c['short'])); ?></a>
                </div>
            </div>
            <aside class="eta-seqs-hero-panel">
                <span><?php esc_html_e('Compliance scope', 'envi-tech-al-modern'); ?></span>
                <?php foreach ($c['scope'] as $item) : ?>
                    <strong><?php echo esc_html($item); ?></strong>
                <?php endforeach; ?>
            </aside>
        </div>
    </section>

    <section class="eta-band eta-band-light">
        <div class="eta-shell eta-seqs-copy-grid">
            <article>
                <p class="eta-eyebrow"><?php echo esc_html(sprintf(__('What is %s', 'envi-tech-al-modern'), $c['short'])); ?></p>
                <h2><?php echo esc_html($c['what_title']); ?></h2>
                <p><?php echo esc_html($c['what']); ?></p>
            </article>
            <article>
                <p class="eta-eyebrow"><?php esc_html_e('Who must comply', 'envi-tech-al-modern'); ?></p>
                <h2><?php echo esc_html($c['who_title']); ?></h2>
                <p><?php echo esc_html($c['who']); ?></p>
            </article>
        </div>
    </section>

    <nav class="eta-band eta-band-light" aria-label="<?php echo esc_attr(sprintf(__('%s tables on this page', 'envi-tech-al-modern'), $c['short'])); ?>">
        <div class="eta-shell">
            <p class="eta-seqs-link-row">
                <?php foreach ($c['tables'] as $table) : ?>
                    <a class="eta-text-link" href="#<?php echo esc_attr($table['id']); ?>"><?php echo esc_html($table['eyebrow']); ?></a>
                <?php endforeach; ?>
            </p>
        </div>
    </nav>

    <?php foreach ($c['tables'] as $table) : ?>
        <?php eta_modern_render_standards_table($table); ?>
    <?php endforeach; ?>

    <section class="eta-band eta-band-light">
        <div class="eta-shell eta-seqs-copy-grid">
            <article>
                <p class="eta-eyebrow"><?php esc_html_e('How compliance works', 'envi-tech-al-modern'); ?></p>
                <h2><?php echo esc_html(sprintf(__('How facilities demonstrate %s compliance', 'envi-tech-al-modern'), $c['short'])); ?></h2>
                <ol class="eta-seqs-process-list">
                    <?php foreach ($c['process'] as $i => $step) : ?>
                        <li><strong><?php echo esc_html(sprintf(__('Step %d: %s', 'envi-tech-al-modern'), $i + 1, $step[0])); ?></strong> <?php echo esc_html($step[1]); ?></li>
                    <?php endforeach; ?>
                </ol>
            </article>
            <article>
                <p class="eta-eyebrow"><?php esc_html_e('Related pages', 'envi-tech-al-modern'); ?></p>
                <h2><?php esc_html_e('Standards, laboratories and reporting', 'envi-tech-al-modern'); ?></h2>
                <ul class="eta-seqs-process-list">
                    <?php foreach ($c['related'] as $r) : ?>
                        <li><a class="eta-text-link" href="<?php echo esc_url(home_url($r[2])); ?>"><?php echo esc_html($r[0]); ?></a> <?php echo esc_html($r[1]); ?></li>
                    <?php endforeach; ?>
                </ul>
            </article>
        </div>
    </section>

    <section id="<?php echo esc_attr($id); ?>-download" class="eta-band eta-seqs-download" aria-labelledby="eta-<?php echo esc_attr($id); ?>-download-title">
        <div class="eta-shell eta-seqs-download-grid">
            <div>
                <p class="eta-eyebrow"><?php esc_html_e('Official documents', 'envi-tech-al-modern'); ?></p>
                <h2 id="eta-<?php echo esc_attr($id); ?>-download-title"><?php echo esc_html(sprintf(__('Download the official %s notifications', 'envi-tech-al-modern'), $c['short'])); ?></h2>
                <p><?php esc_html_e('The tables on this page are a working summary transcribed from the notifications. The authoritative text, including every annex and note, is the official document.', 'envi-tech-al-modern'); ?></p>
            </div>
            <ul class="eta-seqs-process-list">
                <?php foreach ($c['downloads'] as $d) : ?>
                    <?php $href = (strpos($d[1], 'http') === 0) ? $d[1] : home_url($d[1]); ?>
                    <li><a class="eta-text-link" href="<?php echo esc_url($href); ?>"<?php echo (strpos($d[1], 'http') === 0) ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html($d[0]); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>

    <section class="eta-band eta-band-light eta-seqs-faq" aria-label="<?php echo esc_attr(sprintf(__('%s frequently asked questions', 'envi-tech-al-modern'), $c['short'])); ?>">
        <div class="eta-shell">
            <?php eta_modern_section_title('Frequently asked questions', sprintf('%s answers for compliance teams', $c['short'])); ?>
            <div class="eta-faq-answer-grid">
                <?php foreach ($c['faqs'] as $faq) : ?>
                    <details class="eta-faq-item">
                        <summary><?php echo esc_html($faq[0]); ?></summary>
                        <p><?php echo esc_html($faq[1]); ?></p>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="eta-seqs-final">
        <div class="eta-shell eta-seqs-final-grid">
            <div>
                <p class="eta-eyebrow"><?php echo esc_html(sprintf(__('Testing against %s limits and reporting against defined approval conditions', 'envi-tech-al-modern'), $c['short'])); ?></p>
                <h2><?php esc_html_e('From a single effluent sample to a complete monitoring programme: one coordinated team for sampling, scope-confirmed analysis, monitoring and reporting support.', 'envi-tech-al-modern'); ?></h2>
            </div>
            <div class="eta-actions">
                <a class="eta-button" href="<?php echo esc_url(home_url('/contact-us-envi-tech-al/')); ?>"><?php esc_html_e('Request a quotation', 'envi-tech-al-modern'); ?></a>
                <a class="eta-button eta-button-secondary" href="<?php echo esc_url('https://wa.me/923152006074'); ?>" target="_blank" rel="noopener"><?php esc_html_e('WhatsApp consultation', 'envi-tech-al-modern'); ?></a>
            </div>
        </div>
    </section>
    <?php
}
