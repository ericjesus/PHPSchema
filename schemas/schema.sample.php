<?php

class SchemaSample
{
    public const Product = [
      'category'  =>    ['type' => 'Enum', 'required' => true, 'not_empty' => true, 'options' => ['SMARTPHONES','SHOES','CLOTHES']],
      'name'      =>    ['type' => 'String', 'required' => true, 'not_empty' => true, 'max_length' => 20],
      'stock'     =>    ['type' => 'Int', 'required' => true, 'not_empty' => true, 'min_value' => 1]
    ];

    public const Products = [
      'products'    =>    ['type' => [self::Product], 'required' => true, 'not_empty' => true],
    ];
}
