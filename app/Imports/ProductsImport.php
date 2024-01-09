<?php

namespace App\Imports;

use App\Product;
use App\ProductPrice;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        Product::updateOrCreate(
            ['id'                       => @$row['productid']],
            ['kodeprod'                 => @$row['kodeprod'],
            'brand_id'                  => @$row['brand_id'],
            'brand'                     => @$row['brand'],
            'status_herbana'            => @$row['status_herbana'],
            'invoice_name'              => @$row['nama_invoice'],
            'name'                      => @$row['nama_product'],
            // 'large_unit'        => @$row['sat_besar'],
            // 'medium_unit'       => @$row['sat_sedang'],
            // 'small_unit'        => @$row['sat_kecil'],
            // 'large_qty'         => @$row['isi_besar'],
            // 'medium_qty'        => @$row['isi_sedang'],
            // 'small_qty'         => @$row['isi_kecil'],
            'satuan_online'             =>  @$row['satuan_online'],
            'konversi_sedang_ke_kecil'  =>  @$row['konversi_sedang_ke_kecil'],
            'min_pembelian'             =>  @$row['min_pembelian'],
            // 'price_sell'        => @$row['harga_jual'],
            'slug'                      => strtolower(str_replace(" ", "-", @$row['nama_product'])),
            'category_id'               =>  @$row['category_name'],
            'image'                     => @$row['image_product'],
            'status'                    => @$row['status'],
            'status_promosi_coret'      =>  @$row['status_promosi_coret'],
            'status_terbaru'            =>  @$row['status_terbaru'],
            'status_terlaris'           =>  @$row['status_terlaris']]    
        );

        ProductPrice::updateOrCreate(
            ['product_id'                       =>  @$row['productid']],
            ['harga_ritel_gt'                   =>  @$row['harga_ritel_gt'],
            'harga_grosir_mt'                   =>  @$row['harga_grosir_mt'],
            'harga_semi_grosir'                 =>  @$row['harga_semi_grosir'],
            'harga_promosi_coret_ritel_gt'      =>  @$row['harga_promosi_coret_ritel_gt'],
            'harga_promosi_coret_grosir_mt'     =>  @$row['harga_promosi_coret_grosir_mt'],
            'harga_promosi_coret_semi_grosir'   =>  @$row['harga_promosi_coret_semi_grosir']
            // 'medium_retail' =>  @$row['medium_retail'],
            // 'medium_grosir' =>  @$row['medium_grosir'],
            // 'small_retail'  =>  @$row['small_retail'],
            // 'small_grosir'  =>  @$row['small_grosir'],
            // 'small_unit'    =>  @$row['small_unit'],
            // 'price_apps'    =>  @$row['price_apps'],
            // 'price_promo'   =>  @$row['price_promo']
            ]
        );
    }

    public function headingRow(): int
    {
        return 1;
    }
}
