# MAIN Certificate Search

A lightweight certificate search interface for **Medical AI Network (MAIN)**, developed with **WordPress**, **PHP**, and **MySQL/MariaDB**.

This repository documents the application's general functionality. Production configuration and certificate data are maintained separately and are not included in this public documentation.

## Overview

The system helps authorized visitors look up individual and organization certificates through a WordPress page. Matching results display relevant certificate information and, where permitted, a link to the corresponding PDF.

## Features

- Search by recipient name, organization name, or certificate identifier.
- Supports Thai and English text.
- Normalizes spacing and selected Unicode characters for more consistent name matching.
- Requires at least **5 characters** before processing a search.
- Limits the number of displayed results.
- Provides a responsive interface for desktop and mobile devices.
- Uses WordPress's built-in database access and output escaping facilities.

## Technology Stack

| Component | Technology |
| --- | --- |
| Content management | WordPress |
| Application logic | PHP |
| Data storage | MySQL / MariaDB |
| WordPress integration | Code Snippets and shortcode |
| Certificate documents | PDF |

## Deployment Notes

Deployment is handled by the project maintainers in an approved WordPress environment. It requires an authorized certificate dataset, a compatible database schema, the WordPress search snippet, and access-controlled document hosting where appropriate.

**Environment-specific settings are intentionally omitted from this README.** Contact the maintainers for private deployment instructions and configuration details.

## Usage

1. Open the certificate search page provided by your organization.
2. Enter at least five characters from a name, organization, or certificate identifier.
3. Review the matching results.
4. Open a certificate document if the result is available and you are permitted to access it.

## Security and Privacy

- Do not commit production credentials, configuration files, database exports, backups, recipient datasets, or certificate PDFs to a public repository.
- Do not publish operational endpoints, infrastructure details, or internal error messages in issue reports or documentation.
- Use appropriate access control and abuse prevention for any non-public certificate data or documents.
- Handle personal data in accordance with applicable privacy requirements and organizational policies.
- Restrict debugging output to private application logs and authorized administrators.
- Review source files and Git history for sensitive information **before** making the repository public.
- A minimum query length is a usability constraint, **not** a substitute for authorization, rate limiting, or monitoring.

## Maintenance

Application changes should be tested in a non-production environment before deployment. Keep WordPress, PHP, and dependencies updated and review certificate data changes through an approved workflow.

## License

This project is released under the **MIT License**. You may use, modify, and redistribute the code, including for commercial purposes, provided that the original copyright and license notices are retained.

The software is provided **“AS IS”**, without warranty. Refer to the repository’s `LICENSE` file for the full license terms.
