<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class CustomLocation extends Model
{
    protected $fillable = ['name', 'status'];

    public static function getOrCreate(?string $locationText): ?int
    {
        if (!$locationText) {
            return null;
        }

        $locationText = trim($locationText);

        $customLocation = self::firstOrCreate(
            [
                'name'     => $locationText,
            ],
            [
                'status' => 1,
            ]
        );

        return $customLocation->id;
    }
}
