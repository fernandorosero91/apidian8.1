<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'entities';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type_organization_id',
        'name',
        'type_document_identification_id',
        'identification_number',
        'department_id',
        'municipality_id',
        'address',
        'email',
        'legal_representative',
        'phone',
    ];
}
