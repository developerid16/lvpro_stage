<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GetSRPMerchandiseItemList extends Model
{
    use HasFactory;
     protected $table    = 'get_srp_master_list_parameter';
    protected $fillable = ['item_id','item_name','json'];
}
