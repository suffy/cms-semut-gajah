<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Staudenmeir\EloquentEagerLimit\HasEagerLimit;

class Promo extends Model
{    
    use SoftDeletes, HasEagerLimit;
    protected $dates =['deleted_at'];
    protected $table = "promos";
    protected $appends = ["kategori_reward", "spesial"];
    protected $guarded = [];

    public function sku()
    {
        return $this->hasMany('App\PromoSku', 'promo_id', 'id');
    }

    public function reward()
    {
        return $this->hasMany('App\PromoReward', 'promo_id', 'id');
    }

    public function order_detail()
    {
        return $this->hasMany('App\OrderDetail', 'promo_id', 'id');
    }

    public function getKategoriRewardAttribute()
    {
        if($this->category == 1) {
            return "Potongan Harga" ;
        } else 
        if($this->category == 2) {
            return "Bonus Product" ;
        } else {
            return "Potongan Harga & Bonus Product" ;
        }
    }

    public function getSpesialAttribute()
    {
        if($this->special == 1) {
            return "Pembelian pertama";
        } else if ($this->special == 2) {
            return "Promo berlaku sekali";
        }
    }

    // public function getInfoRewardAttribute()
    // {
    //     $list   =   '' ;
    //     if($this->category == 1) {
    //         foreach ($this->reward as $reward_data) {
    //             if($reward_data->reward_disc != null) {
    //                 $list   .=  "Diskon " . $reward_data->reward_disc . "%" ;
    //             }
    //             else if($reward_data->reward_nominal != null) {
    //                 $list   .=  "Potongan Rp. " . $reward_data->reward_nominal;
    //             } else if($reward_data->reward_point != null) {
    //                 $list   .=  $reward_data->reward_point . "Point";
    //             }
    //         }
    //     } else if($this->category == 2){
    //         foreach ($this->reward as $reward_data) {
    //             $list   .=  "-" . $reward_data->product->name . " " . $reward_data->reward_qty . " " . $reward_data->satuan;   
    //         }
    //     } else {
    //         foreach ($this->reward as $reward_data) {
    //             if($reward_data->reward_disc != null){
    //                 $list .= "Diskon " . $reward_data->reward_disc . "%";
    //             } else if($reward_data->reward_nominal != null) {
    //                 $list .= "Potongan Rp. " . number_format($reward_data->reward_nominal);
    //             } else if($reward_data->reward_point != null) {
    //                 $list .= $reward_data->reward_point . " Point";
    //             } else if($reward_data->reward_product_id != null) {
    //                 $list .= $reward_data->product->name . " " . "(" . $reward_data->reward_qty . " " . $reward_data->satuan . ")";  
    //             }
    //         }                                         
    //     }
    //     return $list ;    
    // }

    // public function getTermConditionPromoAttribute()
    // {
    //     $list   =   '' ;
    //     if($this->termcondition == 1){
    //         if($this->detail_termcondition == 1) {
    //             $list .= "min : " . $this->min_qty . " item";
    //         } else {
    //             foreach($this->sku as $row_sku) {
    //                 $list .= "\n" . "min : " .$row_sku->product->name . " ( " . $row_sku->min_qty . " " . $row_sku->satuan . " )";
    //             }
    //         }
    //     } else if($this->termcondition == 2) {
    //         $list .= "min : Rp. " . number_format($this->min_transaction);
    //     } else {
    //         if($this->detail_termcondition == 1) {
    //             $list .= "min : " . $this->min_qty . " item";
    //         } else {
    //             foreach($row->sku as $row_sku) {
    //                 $list .= "\n" . "min : " . $row_sku->product->name . "( " . $row_sku->min_qty . " " . $row_sku->satuan . " )";
    //             }
    //         }
    //         $list .= "\n" . "min : Rp. " . number_format($this->min_transaction);
    //     }
    //     return $list;    
    // }
}
