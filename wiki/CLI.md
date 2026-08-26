# CLI

## Ingest

All ingest related commands start with `php artisan ingest:`
This is the only command that can talk to the outside world and the Interwebs

### NVD

The only command needed here is `php artisan ingest:nvd`

On the first run or with the `--full` flag it takes all data that NVD has to offer, otherwise it only takes data that changed since the last ingest

### OSV

As of now there is `php artisan ingest:osv` 
This downloads all.zip, from any specified ecosystem. Reffer to [this](https://google.github.io/osv.dev/data/#ecosystem-naming) and [this](https://storage.googleapis.com/osv-vulnerabilities/ecosystems.txt)
If left unspecified it downloads the root all.zip

And `php artisan ingest:osv-sync`
checks modified_id.csv and downloads and updates only the modified entries since the last ingest
