<?php

/*
 * This file is part of ShopCli.
 *
 * (c) JTL-Software GmbH <info@jtl-software.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace ShopCli\Faker;

use Faker\Provider\Base as FakerBase;

class Commerce extends FakerBase
{
    protected static $department = array(
        'Books', 'Movies', 'Music', 'Games', 'Electronics', 'Computers', 'Home', 'Garden', 'Tools', 'Grocery', 'Health', 'Beauty',
        'Toys', 'Kids', 'Baby', 'Clothing', 'Shoes', 'Jewelery', 'Sports', 'Outdoors', 'Automotive', 'Industrial',
    );

    protected static $adjective = array(
        'Small', 'Ergonomic', 'Rustic', 'Intelligent', 'Gorgeous', 'Incredible', 'Fantastic', 'Practical', 'Sleek', 'Awesome',
        'Enormous', 'Mediocre', 'Synergistic', 'Heavy Duty', 'Lightweight', 'Aerodynamic', 'Durable',
    );

    protected static $material = array(
        'Steel', 'Wooden', 'Concrete', 'Plastic', 'Cotton', 'Granite', 'Rubber', 'Leather', 'Silk', 'Wool', 'Linen', 'Marble', 'Iron',
        'Bronze', 'Copper', 'Aluminum', 'Paper',
    );

    protected static $product = array(
        'Chair', 'Car', 'Computer', 'Gloves', 'Pants', 'Shirt', 'Table', 'Shoes', 'Hat', 'Plate', 'Knife', 'Bottle', 'Coat', 'Lamp',
        'Keyboard', 'Bag', 'Bench', 'Clock', 'Watch', 'Wallet',
    );

    public function productName()
    {
        return static::randomElement(static::$adjective).' '.static::randomElement(static::$material).' '.static::randomElement(static::$product);
    }

    public function department()
    {
        return static::randomElement(static::$department);
    }

    public function material()
    {
        return static::randomElement(static::$material);
    }
}
