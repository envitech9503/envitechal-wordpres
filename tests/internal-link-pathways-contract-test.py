#!/usr/bin/env python3

"""Guard the two high-value contextual internal-link pathways."""

from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]


def main() -> None:
    front = (ROOT / 'wp-content/themes/generatepress-envitechal/front-page.php').read_text(encoding='utf-8')
    functions = (ROOT / 'wp-content/themes/generatepress-envitechal/functions.php').read_text(encoding='utf-8')

    industry_start = front.index("eta-home-industries")
    industry_end = front.index("eta-home-maritime", industry_start)
    industry = front[industry_start:industry_end]
    if "home_url('/karachi-environmental-lab/')" not in industry:
        raise AssertionError('homepage industry context must link to the Karachi laboratory page')
    if 'Review environmental laboratory support in Karachi' not in industry:
        raise AssertionError('homepage Karachi link must have useful visible anchor text')

    heading_position = functions.index('Related pathways')
    section_start = functions.rfind('<nav ', 0, heading_position)
    if section_start < 0:
        raise AssertionError('national FAQ related pathways must use navigation semantics')
    section_end = functions.index('eta-utility-final', section_start)
    related = functions[section_start:section_end]
    required_paths = (
        '/services/analytical-lab-services/',
        '/services/water-testing-lab-services/',
        '/karachi-environmental-lab/',
        '/lahore-environmental-lab/',
        '/accreditations-certifications/',
        '/report-verification-portal/',
    )
    for path in required_paths:
        if f"home_url('{path}')" not in related:
            raise AssertionError(f'national FAQ related pathways are missing {path}')
    if '<nav ' not in related or 'aria-label=' not in related:
        raise AssertionError('national FAQ related pathways must use labelled navigation semantics')


if __name__ == '__main__':
    main()
