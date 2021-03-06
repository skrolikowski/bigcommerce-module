<?php

/*
 * This file is part of the FusionCMS application.
 *
 * (c) efelle creative <appdev@efelle.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Faker\Generator as Faker;
use Modules\Bigcommerce\Models\Product;
use Modules\Bigcommerce\Models\ProductVariant;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(ProductVariant::class, function (Faker $faker) {
    static $order = 1;

	$price    = $faker->randomFloat(2, 25, 100); // $
	$markup   = $faker->randomFloat(2, 15, 25);  // %
	$cost     = $faker->randomFloat(2, 10, 15);  // %
	$discount = $faker->randomFloat(2, 5, 15);   // %

	$cost_price       = $price + $price * ($cost / $price);
	$retail_price     = $price + $price * ($markup / $price);
	$sale_price       = $price - $price * ($discount / $price);
	$calculated_price = $faker->randomElement([$retail_price, $sale_price]);

    return [
        'id'               => $order++,
        'product_id'       => factory(Product::class)->create(),
        'sku'              => $faker->isbn10,
        'weight'           => $faker->numberBetween(1, 10),
        'width'            => $faker->numberBetween(1, 10),
        'height'           => $faker->numberBetween(1, 10),
        'depth'            => $faker->numberBetween(1, 10),
        'price'            => $price,
        'cost_price'       => $cost_price,
        'retail_price'     => $retail_price,
        'sale_price'       => $sale_price,
        'calculated_price' => $calculated_price,
        
        'inventory_level'         => $faker->numberBetween(0, 100),
        'inventory_warning_level' => $faker->numberBetween(0, 5),
    ];
});