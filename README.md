# Healthdata Timesheet module

## Description

This module provide a new revisionable fieldable translatable content entity called Timesheet (timesheet).
Also, it provide a migration group with a pre-built migration template for CSV file.

For this migration, you need to put CSV file into `public://import-sources/` path with the file name `timesheets.csv`.
The CSV file must contain the following columns :

- tsid
- langcode
- title
- year
- week
- total_time
- user

When you enable the module a sample of 10 timesheets are directly imported from the module.

## Requirements

- Drupal 9
- Migrate Source CSV (migrate_source_csv)
- Migrate Tools (migrate_tools)
- Migrate Plus (migrate_plus)

### Optional

If you want to use the translation on the timesheet content entity. don't forget to
install content translation modules and enable the translation capabilities on the new entity.
