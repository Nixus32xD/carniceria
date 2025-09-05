<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Cut;
use App\Models\Product;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 🔹 Crear categorías
        $categories = [
            'Res',
            'Cerdo',
            'Pollo',
            'Embutidos'
        ];

        $categoriesMap = [];
        foreach ($categories as $cat) {
            $categoriesMap[strtolower($cat)] = Category::create(['name' => $cat])->id;
        }

        // 🔹 Crear cortes con su categoría
        $cuts = [
            'Bife de Chorizo' => 'Res',
            'Pechuga' => 'Pollo',
            'Costillas' => 'Cerdo',
            'Chorizo' => 'Embutidos',
            'Lomo' => 'Res',
            'Muslo' => 'Pollo',
            'Jamón' => 'Cerdo',
            'Molida' => 'Res',
        ];

        $cutsMap = [];
        foreach ($cuts as $cut => $category) {
            $cutsMap[strtolower($cut)] = Cut::create([
                'name' => $cut,
                'category_id' => $categoriesMap[strtolower($category)]
            ])->id;
        }


        // 🔹 Crear productos (CORREGIDO - offerPrice debe ser menor que price)
        $products = [
            [
                'name'        => 'Bife de Chorizo',
                'price'       => 3000.00, // Precio original
                'offerPrice'  => 2850.00, // Precio de oferta (MENOR)
                'discount'    => 5,
                'stock'       => 20,
                'isActive'    => true,
                'isOffer'     => true,
                'category_id' => $categoriesMap['res'],
                'cut_id'      => $cutsMap['bife de chorizo'],
                'image'       => 'bife-chorizo.jpg',
                'image_alt'   => 'Corte de bife de chorizo jugoso',
            ],
            [
                'name'        => 'Pechuga de Pollo',
                'price'       => 2000.00,
                'offerPrice'  => null,
                'discount'    => 0,
                'stock'       => 15,
                'isActive'    => true,
                'isOffer'     => false,
                'category_id' => $categoriesMap['pollo'],
                'cut_id'      => $cutsMap['pechuga'],
                'image'       => 'pechuga-pollo.jpg',
                'image_alt'   => 'Pechuga de pollo fresca',
            ],
            [
                'name'        => 'Costillas de Cerdo',
                'price'       => 2500.00, // Precio original
                'offerPrice'  => 2375.00, // Precio de oferta (MENOR)
                'discount'    => 5,
                'stock'       => 10,
                'isActive'    => true,
                'isOffer'     => true,
                'category_id' => $categoriesMap['cerdo'],
                'cut_id'      => $cutsMap['costillas'],
                'image'       => 'costillas-cerdo.jpg',
                'image_alt'   => 'Costillas de cerdo tiernas',
            ],
            [
                'name'        => 'Chorizo Casero',
                'price'       => 1500.00,
                'offerPrice'  => null,
                'discount'    => 0,
                'stock'       => 8,
                'isActive'    => true,
                'isOffer'     => false,
                'category_id' => $categoriesMap['embutidos'],
                'cut_id'      => $cutsMap['chorizo'],
                'image'       => 'chorizo-casero.jpg',
                'image_alt'   => 'Chorizo casero artesanal',
            ],
            [
                'name'        => 'Lomo de Res',
                'price'       => 4000.00,
                'offerPrice'  => null,
                'discount'    => 0,
                'stock'       => 8,
                'isActive'    => true,
                'isOffer'     => false,
                'category_id' => $categoriesMap['res'],
                'cut_id'      => $cutsMap['lomo'],
                'image'       => 'lomo-res.jpg',
                'image_alt'   => 'Lomo de res premium',
            ],
            [
                'name'        => 'Muslos de Pollo',
                'price'       => 3500.00,
                'offerPrice'  => null,
                'discount'    => 0,
                'stock'       => 9,
                'isActive'    => true,
                'isOffer'     => false,
                'category_id' => $categoriesMap['pollo'],
                'cut_id'      => $cutsMap['muslo'],
                'image'       => 'muslos-pollo.jpg',
                'image_alt'   => 'Muslos de pollo frescos',
            ],
            [
                'name'        => 'Jamón Serrano',
                'price'       => 6000.00,
                'offerPrice'  => null,
                'discount'    => 0,
                'stock'       => 4,
                'isActive'    => true,
                'isOffer'     => false,
                'category_id' => $categoriesMap['embutidos'],
                'cut_id'      => $cutsMap['jamón'],
                'image'       => 'jamon-serrano.jpg',
                'image_alt'   => 'Jamón serrano curado',
            ],
            [
                'name'        => 'Carne Molida Premium',
                'price'       => 3000.00, // Precio original
                'offerPrice'  => 2760.00, // Precio de oferta (MENOR)
                'discount'    => 8,
                'stock'       => 20,
                'isActive'    => true,
                'isOffer'     => true,
                'category_id' => $categoriesMap['res'],
                'cut_id'      => $cutsMap['molida'],
                'image'       => 'carne-molida.jpg',
                'image_alt'   => 'Carne molida premium',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
