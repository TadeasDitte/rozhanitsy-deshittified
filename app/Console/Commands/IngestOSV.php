<?php

namespace App\Console\Commands;

use App\Models\Source;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use ZipArchive;

#[Signature('ingest:osv')]
#[Description('Pull vulnerabilities from OSV')]
class IngestOSV extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
    }
}
