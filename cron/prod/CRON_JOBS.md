# Cron Jobs Documentation

This repository contains Python integration scripts that are intended to be executed by cron, but no cron schedule (`crontab`) entries are stored in this codebase.

## Job Inventory

| Job Script | Purpose | Key Actions | Main Dependencies |
|---|---|---|---|
| `import_pim_server.py` | Import product data from PIM into portal DB and copy related media/files | Calls PIM APIs, writes JSON into `import` table, runs `sp_migrate_data_from_pim_v11`, copies files from SFTP, calls finish endpoint | `requests`, `mysql.connector`, `pysftp` |
| `import_prices.py` | Import SAP prices into staging | Fetches SAP price feed, inserts records into `stg_prices` | `requests`, `mysql.connector` |
| `MovePricesFromSTGToProd.py` | Move staged prices into production import DB and trigger price sync | Moves rows to `import_gi_prod.stg_prices`, executes `sp_ImportPricesFromSAP_v4(1)` | `mysql.connector` |
| `import_stocks.py` | Import SAP stock into staging/import DB and sync into portal | Fetches SAP stock feed, inserts into `stg_stocks`, runs `sp_insertStocksIntoPortal` | `requests`, `mysql.connector` |
| `import_invoices.py` | Import invoices and copy invoice PDFs | Reads `stg_pim_gi.stg_invoices`, copies files from SFTP, updates source as imported, runs `sp_SyncInvoices()` | `mysql.connector`, `pysftp` |

## DB Procedures Triggered

- `sp_migrate_data_from_pim_v11` (from `import_pim_server.py`)
- `sp_ImportPricesFromSAP_v4(1)` (from `MovePricesFromSTGToProd.py`)
- `sp_insertStocksIntoPortal` (from `import_stocks.py`)
- `sp_SyncInvoices()` (from `import_invoices.py`)

## Cron Command Templates

Adjust Python path and working directory to your server setup.

```cron

25 11 * * * python3 /home/datext_infola_jos/python-new/import_pim_server.py

#31 15 * * * python3 /home/datext_infola_jos/python-new/import_pim_server.py
# >> /home/datext_infola_jos/pythonlog.txt 2>&1

0 5 * * * python3  /home/datext_infola_jos/python-new/MovePricesFromSTGToProd_DEV.py

*/5 * * * * wget -q -O - https://localhost/wp-cron.php?doing_wp_cron >/dev/null 2>&1

```

## Operations Notes

- Create and maintain `logs/` with appropriate write permissions.
- These scripts currently contain hardcoded credentials/endpoints; move them to environment variables before production hardening.
- Confirm dependency order between price jobs (`import_prices.py` should run before `MovePricesFromSTGToProd.py`).
- Confirm desired frequency with business owners; schedule values above are templates because no official cron expressions are stored in this repo.
