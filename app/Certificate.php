<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'password', 'expiration_date',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'company_id', 'path', 'password',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'path',
    ];

    /**
     * Get the path to the certificate file.
     *
     * @return string
     */
    public function getPathAttribute()
    {
        return storage_path("app/certificates/{$this->name}");
    }

    /**
     * Get the company that owns the certificate.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Check if certificate is expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        if (!$this->expiration_date) {
            return false;
        }
        return now()->greaterThan($this->expiration_date);
    }

    /**
     * Get days until expiration.
     *
     * @return int|null
     */
    public function daysUntilExpiration(): ?int
    {
        if (!$this->expiration_date) {
            return null;
        }
        return now()->diffInDays($this->expiration_date, false);
    }
}
