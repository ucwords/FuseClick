<?php
namespace App\Models\LocalOffer;

use Illuminate\Database\Eloquent\Model;

class FuseOffer extends Model
{
	public $timestamps = false;
    protected $table = "fuse_offer";

    protected $guarded = array();
}