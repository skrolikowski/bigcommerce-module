<?php

/*
 * This file is part of the FusionCMS application.
 *
 * (c) efelle creative <appdev@efelle.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Modules\Bigcommerce\Jobs\Requests\Catalog;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Bigcommerce\Models\ProductModifier;
use Modules\Bigcommerce\Models\ProductModifierValue;

class ProductModifierRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    /**
     * API request endpoint.
     * 
     * @var string
     */
    private $endpoint = "v3/catalog/products/{product_id}/modifiers";

    /**
     * Request method.
     * 
     * @var string
     */
    private $method = "GET";

    /**
     * Request query - page number.
     * 
     * @var integer
     */
    private $page = 1;

    /**
     * Request query - limit.
     * 
     * @var integer
     */
    private $limit = 100;

    /**
     * Product ID.
     *
     * @var integer
     */
    private $productId;

    /**
     * Request query.
     * 
     * @var array
     */
    private $query = [];

    /**
     * Create a new job instance.
     *
     * @param  integer $productId
     * @param  array   $query
     * @return void
     */
    public function __construct($productId, array $query = [])
    {
        $this->productId = $productId;
        $this->endpoint = str_replace('{product_id}', $productId, $this->endpoint);
        $this->query    = array_merge([
            'page'    => $this->page,
            'limit'   => $this->limit
        ], $query);
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $response   = resolve('requestor')->request($this->endpoint, $this->method, [ 'query' => $this->query ]);
        $items      = $response->get('data');
        $pagination = $response->get('pagination');

        $items->each(function($item, $key) {
            $this->createOrUpdate($item);
        });

        // BigCommerce only allows a max of 250 items to be pulled at a time.
        // We'll use the `pagination` value to cycle through the next set..
        if (! is_null($pagination)) {
            if ($pagination->hasMorePages()) {
                $newQuery = $this->query;
                $newQuery['page'] = $pagination->currentPage() + 1;

                dispatch(new ProductModifierRequest($this->productId, $newQuery));
            }
        }
    }

    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        Log::error('[BigCommerce API Request]', ['message' => $exception->getMessage()]);
    }

    /**
     * Create or update database entry.
     *
     * @param  Collection $item
     * @return void
     */
    public function createOrUpdate($item)
    {
        ProductModifier::updateOrCreate([ 'id' => $item->id ],
            [
                'product_id'   => $item->product_id,
                'name'         => $item->name,
                'display_name' => $item->display_name,
                'type'         => $item->type,
                'required'     => (bool) $item->required,
                'config'       => $item->config,
                'sort_order'   => $item->sort_order,
            ]
        );

        collect($item->option_values)->map(function($data, $key) use ($item) {
            $this->createOrUpdateProductModifierValue($item->id, $data);
        });
    }

    /**
     * Create or update database entry.
     *
     * @param  integer    $modifierId
     * @param  Collection $item
     * @return void
     */
    public function createOrUpdateProductModifierValue($modifierId, $item)
    {
        ProductModifierValue::updateOrCreate([ 'id' => $item->id ],
            [
                'modifier_id'  => $modifierId,
                'label'        => $item->label,
                'is_default'   => (bool) $item->is_default,
                'value_data'   => $item->value_data,
                'adjusters'    => $item->adjusters,
                'sort_order'   => $item->sort_order,
            ]
        );
    }
}
