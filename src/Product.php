<?php

namespace App;

class Product
{
    public $title;
    public $price;
    public $imageUrl;
    public $capacityMB;
    public $colour;
    public $availabilityText;
    public $isAvailable;
    public $shippingText;

    public function __construct($title, $price, $imageUrl, $capacityMB, $colour, $availabilityText, $isAvailable, $shippingText)
    {
        $this->title = $title;
        $this->price = $price;
        $this->imageUrl = $imageUrl;
        $this->capacityMB = $capacityMB;
        $this->colour = $colour;
        $this->availabilityText = $availabilityText;
        $this->isAvailable = $isAvailable;
        $this->shippingText = $shippingText;
    }
}
