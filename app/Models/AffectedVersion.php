<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $vulnerability_id
 * @property string|null $version_start
 * @property bool $version_start_including
 * @property string|null $version_end
 * @property bool $version_end_including
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */

#[Fillable(['vulnerability_id', 'version_start', 'version_start_including', 'version_end', 'version_end_including'])]

class AffectedVersion extends Model
{

}
