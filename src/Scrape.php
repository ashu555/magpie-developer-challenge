<?php

namespace App;

require __DIR__ . '/../vendor/autoload.php';
use Symfony\Component\DomCrawler\Crawler;

class Scrape
{
    private $products = [];
    private $productIds = []; // To store unique product identifiers (to avoid duplicates)

    public function run(): void
    {
        $page = 1;

        // Keep fetching data from pages until there are no more pages
        do {
            $crawler = ScrapeHelper::fetchDocument('https://www.magpiehq.com/developer-challenge/smartphones?page=' . $page);
            $productCount = 0;

            // Fetch products on the current page
            $crawler->filter('div.product')->each(function (Crawler $node) use (&$productCount) {

                // Initialize variables to default values
                $title = $price = $imageUrl = $capacity = $colour = $availabilityText = $shippingText = $shippingDate = null;
                $isAvailable = false;

                if ($node->filter('.product-name')->count()) {
                    $title = $node->filter('.product-name')->text();
                }
                if ($node->filter('.my-8.block.text-center.text-lg')->count()) {
                    $price = ($node->filter('.my-8.block.text-center.text-lg')->text());
                }
                $baseUrl = 'https://www.magpiehq.com/developer-challenge'; // The base URL of the website you're scraping

                if ($node->filter('img')->count()) {
                    $imageUrl = $node->filter('img')->attr('src');

                    // Check if the URL is relative, and prepend the base URL if necessary
                    if (strpos($imageUrl, 'http') !== 0) {
                        $imageUrl = $this->normalizeUrl($imageUrl, $baseUrl);
                        
                    }
                }


                if ($node->filter('.product-capacity')->count()) {
                    $capacity = $node->filter('.product-capacity')->text();
                }
                if ($node->filter('span[data-colour]')->count()) {
                    $colour = $node->filter('span[data-colour]')->attr('data-colour');
                }


                $elements = $node->filter('.my-4.text-sm.block.text-center');

            // Check if there are at least two occurrences of the class (one for availability and one for shipping)
            if ($elements->count() >= 2) {
                // The first occurrence (index 0) is for availability
                $availabilityText = $elements->eq(0)->text();
                $availabilityText = str_replace('Availability: ', '', $availabilityText); 
                $isAvailable = strpos($availabilityText, 'Out of Stock') === false;

                // The second occurrence (index 1) is for shipping information
                $shippingText = $elements->eq(1)->text();
            }

            // If there are fewer than two elements, handle accordingly (e.g., only availability is present)
            if ($elements->count() == 1) {
                $availabilityText = $elements->eq(0)->text();
                $availabilityText = str_replace('Availability: ', '', $availabilityText); 
                $isAvailable = strpos($availabilityText, 'Out of Stock') === false;
            }



            

                // Only add the product if it has a title and is unique
                if ($title) {
                    // Create a unique identifier for each product variant (title + capacity + color)
                    $productId = $title . '-' . $capacity . '-' . $colour;

                    // Check for duplicates
                    if (!in_array($productId, $this->productIds)) {
                        // Add the unique product identifier to the array
                        $this->productIds[] = $productId;

                        // Create the product object
                        $product = new Product(
                            $title, $price, $imageUrl, $this->convertToMB($capacity), $colour, 
                            $availabilityText, $isAvailable, $shippingText
                        );

                        // Add the product to the list
                        $this->products[] = $product;
                    }

                    $productCount++;
                }
            });

            $page++;
        } while ($productCount > 0); // Continue until no products are found on the page
        // Save the products to a JSON file
        file_put_contents('output.json', json_encode($this->products));

        echo 'Data imported Successfully';
    }

    private function convertToMB($capacityString) {
        $capacityGB = intval($capacityString); // Convert from GB to MB
        return $capacityGB * 1024;
    }

    private function normalizeUrl($relativeUrl, $baseUrl) {
    // Remove '../' from the relative URL
    $relativeUrl = preg_replace('#\.\./#', '', $relativeUrl);

    // Concatenate the base URL with the cleaned relative URL
    return rtrim($baseUrl, '/') . '/' . ltrim($relativeUrl, '/');
    }
}

$scrape = new Scrape();
$scrape->run();
