<?php

namespace App\Models;

use Database\Factories\FormatFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'version'])]
class Format extends Model
{
    /** @use HasFactory<FormatFactory> */
    use HasFactory;
}
