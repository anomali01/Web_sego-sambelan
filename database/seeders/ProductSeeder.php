<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Sego Sambelan Komplit',
                'slug' => 'sego-sambelan-komplit',
                'description' => 'Nasi putih hangat dengan aneka sambal (sambal terasi, sambal ijo, sambal bawang), lauk ayam goreng, tempe goreng, tahu goreng, lalapan segar, dan kerupuk.',
                'category' => 'food',
                'price' => 25000,
                'is_available' => true,
                'stock' => 50,
            ],
            [
                'name' => 'Sego Sambelan Ayam Geprek',
                'slug' => 'sego-sambelan-ayam-geprek',
                'description' => 'Nasi putih dengan ayam geprek sambal bawang yang pedas mantap, dilengkapi lalapan dan kerupuk.',
                'category' => 'food',
                'price' => 22000,
                'is_available' => true,
                'stock' => 40,
            ],
            [
                'name' => 'Sego Sambelan Lele',
                'slug' => 'sego-sambelan-lele',
                'description' => 'Nasi putih hangat dengan lele goreng krispi, sambal terasi, lalapan timun dan kemangi.',
                'category' => 'food',
                'price' => 20000,
                'is_available' => true,
                'stock' => 35,
            ],
            [
                'name' => 'Sego Sambelan Cumi',
                'slug' => 'sego-sambelan-cumi',
                'description' => 'Nasi putih dengan cumi goreng tepung, sambal ijo pedas, dan lalapan segar.',
                'category' => 'food',
                'price' => 28000,
                'is_available' => true,
                'stock' => 30,
            ],
            [
                'name' => 'Ayam Goreng Kremes',
                'slug' => 'ayam-goreng-kremes',
                'description' => 'Ayam goreng dengan taburan kremesan gurih renyah. Cocok untuk lauk tambahan.',
                'category' => 'food',
                'price' => 15000,
                'is_available' => true,
                'stock' => 25,
            ],
            [
                'name' => 'Es Teh Manis',
                'slug' => 'es-teh-manis',
                'description' => 'Teh manis dingin segar, teman sempurna untuk makan sego sambelan.',
                'category' => 'drink',
                'price' => 5000,
                'is_available' => true,
                'stock' => 100,
            ],
            [
                'name' => 'Es Jeruk Segar',
                'slug' => 'es-jeruk-segar',
                'description' => 'Jeruk peras asli dengan es batu, manis segar alami.',
                'category' => 'drink',
                'price' => 8000,
                'is_available' => true,
                'stock' => 80,
            ],
            [
                'name' => 'Jus Alpukat',
                'slug' => 'jus-alpukat',
                'description' => 'Jus alpukat kental creamy dengan susu coklat. Minuman favorit!',
                'category' => 'drink',
                'price' => 12000,
                'is_available' => true,
                'stock' => 40,
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['slug' => $product['slug']],
                $product
            );
        }
    }
}
