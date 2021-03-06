<?php

namespace Modules\Bigcommerce\Models;

use Fusion\Concerns\CachesQueries;
use Fusion\Database\Eloquent\Model;

class Store extends Model
{
    use CachesQueries;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bigcommerce_store_settings';

    /**
     * primaryKey 
     * 
     * @var string
     */
    protected $primaryKey = 'key';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
	 * The attributes that aren't mass assignable.
	 *
	 * @var array
	 */
    protected $guarded = [];

    /**
     * Get the store setting's decoded JSON value.
     *
     * @param  string  $value
     * @return mixed
     */
    public function getValueAttribute($value)
    {
        return json_decode($value);
    }

    /**
     * Store the store setting's value as JSON.
     *
     * @param  string  $value
     * @return void
     */
    public function setValueAttribute($value)
    {
        $this->attributes['value'] = json_encode($value);
    }
}
