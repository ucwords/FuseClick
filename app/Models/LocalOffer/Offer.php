<?php
namespace App\Models\LocalOffer;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
	public $timestamps = false;
    protected $table = "base_offer";

    protected $guarded = array();
}