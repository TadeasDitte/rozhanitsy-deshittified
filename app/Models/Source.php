<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $url
 * @property string|null $ingest_base_url
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
**/

#[Fillable(['name', 'slug', 'url', 'ingest_base_url'])]

class Source extends Model
{

}
