<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppUpdate extends Model
{
    protected $table = "app_updates";

    protected $fillable = [
        "version_code",
        "version_name",
        "device_platform",
        "apk_url",
        "changelogs"
    ];

    protected $casts = [
        "id" => "integer",
        "version_code" => "integer",
        "created_at" => "datetime",
        "updated_at" => "datetime",
    ];
}
