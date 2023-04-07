<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Source extends Model
{
    protected $table = "sources";

    protected $fillable = [
        "name", "description", "language",
        "data_source_id", "specialization", "country",
        "web_url", "str_id"
    ];

    /**
     * Get the datasource that owns this news source.
     */
    public function dataSource(): BelongsTo
    {
        return $this->belongsTo(DataSource::class, 'data_source_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            "user_source",
            "source_id",
            "user_id"
        );
    }
}
