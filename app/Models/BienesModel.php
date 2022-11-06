<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BienesModel extends Model
{
    protected $table = 'bienes';

    protected $fillable = [
        'articulo',
        'descripcion',
        'id_usuario',
    ];

    public static function crearBien($articulo, $descripcion, $id_usuario)
    {
      return  BienesModel::create(
                [
                    'articulo' => $articulo,
                    'descripcion' => $descripcion,
                    'id_usuario' => $id_usuario,
                ]
            );
    }

    public static function actualizarBien($id,$articulo, $descripcion, $id_usuario)
    {
        return  BienesModel::where('id',$id)
        ->update(
            [
                'articulo' => $articulo,
                'descripcion' => $descripcion,
                'id_usuario' => $id_usuario,
            ]
        );
    }

    public static function borrarBien($id)
    {
        BienesModel::where('id',$id)->delete();
        return true;
    }

    public static function getBienes($id_bienes)
    {
        return BienesModel::whereIn('id', $id_bienes)->get();
    }
}
